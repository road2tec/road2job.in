<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class OtpVerification extends Model
{
    protected static string $table = 'otp_verifications';
    protected static bool $timestamps = false;

    public static function create(int $userId, string $phone, string $otp, string $purpose, int $expiryMinutes): string
    {
        return static::insert([
            'user_id' => $userId,
            'phone' => $phone,
            'otp_code' => $otp,
            'purpose' => $purpose,
            'expires_at' => date('Y-m-d H:i:s', time() + $expiryMinutes * 60),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function latestFor(int $userId, string $purpose): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM otp_verifications
             WHERE user_id = :user_id AND purpose = :purpose AND verified_at IS NULL
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId, 'purpose' => $purpose]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function incrementAttempts(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }

    public static function markVerified(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE otp_verifications SET verified_at = :now WHERE id = :id"
        );

        return $stmt->execute(['id' => $id, 'now' => date('Y-m-d H:i:s')]);
    }
}
