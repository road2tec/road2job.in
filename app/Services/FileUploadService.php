<?php

namespace App\Services;

use RuntimeException;

class FileUploadService
{
    protected const ALLOWED = [
        'resume' => ['pdf', 'doc', 'docx'],
        'avatar' => ['jpg', 'jpeg', 'png', 'webp'],
        'document' => ['pdf', 'jpg', 'jpeg', 'png'],
        'logo' => ['jpg', 'jpeg', 'png', 'webp'],
        'cover' => ['jpg', 'jpeg', 'png', 'webp'],
        'gallery' => ['jpg', 'jpeg', 'png', 'webp'],
        'video' => ['webm', 'mp4'],
    ];

    protected const MIME_MAP = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'webm' => ['video/webm'],
        'mp4' => ['video/mp4'],
    ];

    protected const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    protected const MAX_SIZE_OVERRIDES = [
        // Sized for one continuous multi-question interview recording (up to
        // ~11 minutes worst case: 2 technical + 2 hr @90s + 1 coding @300s,
        // recorder keeps running through the coding question too) at the
        // recorder's 500kbps setting (~41MB worst case) plus headroom.
        'video' => 50 * 1024 * 1024, // 50MB
    ];

    protected const COMPRESSIBLE_TYPES = ['avatar', 'logo', 'cover', 'gallery'];
    protected const COMPRESS_MAX_DIMENSION = 1600;
    protected const COMPRESS_JPEG_QUALITY = 82;
    protected const COMPRESS_PNG_COMPRESSION = 6;
    protected const COMPRESS_WEBP_QUALITY = 82;

    public function upload(array $file, string $type): string
    {
        if (!isset(self::ALLOWED[$type])) {
            throw new RuntimeException("Unknown upload type: {$type}");
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed.');
        }

        $maxSize = self::MAX_SIZE_OVERRIDES[$type] ?? self::MAX_SIZE;

        if ($file['size'] > $maxSize) {
            throw new RuntimeException('File exceeds the ' . (int) ($maxSize / (1024 * 1024)) . 'MB size limit.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED[$type], true)) {
            throw new RuntimeException('File type not allowed for ' . $type . '.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = self::MIME_MAP[$extension] ?? [];

        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('File content does not match its extension.');
        }

        $directory = WEB_ROOT . "/uploads/{$type}s";

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Could not save uploaded file.');
        }

        if (in_array($type, self::COMPRESSIBLE_TYPES, true)) {
            $this->compressImage($destination, $extension);
        }

        return "uploads/{$type}s/{$filename}";
    }

    /**
     * Best-effort - a failure here must never undo an already-successful
     * upload, so every failure mode just leaves the original file in place.
     */
    protected function compressImage(string $absolutePath, string $extension): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        try {
            $image = match ($extension) {
                'jpg', 'jpeg' => imagecreatefromjpeg($absolutePath),
                'png' => imagecreatefrompng($absolutePath),
                'webp' => imagecreatefromwebp($absolutePath),
                default => null,
            };

            if ($image === false || $image === null) {
                return;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            if ($width > self::COMPRESS_MAX_DIMENSION) {
                $newWidth = self::COMPRESS_MAX_DIMENSION;
                $newHeight = (int) round($height * ($newWidth / $width));

                $resized = imagecreatetruecolor($newWidth, $newHeight);

                if ($extension === 'png') {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                }

                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }

            match ($extension) {
                'jpg', 'jpeg' => imagejpeg($image, $absolutePath, self::COMPRESS_JPEG_QUALITY),
                'png' => imagepng($image, $absolutePath, self::COMPRESS_PNG_COMPRESSION),
                'webp' => imagewebp($image, $absolutePath, self::COMPRESS_WEBP_QUALITY),
                default => null,
            };

            imagedestroy($image);
        } catch (\Throwable $e) {
            // Original upload already succeeded - compression is a bonus, not a requirement.
        }
    }
}
