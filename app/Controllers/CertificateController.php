<?php

namespace App\Controllers;

use App\Models\StudentCertificate;
use App\Services\FileUploadService;
use Core\Request;
use Core\Session;

class CertificateController extends StudentSubResourceController
{
    protected function modelClass(): string
    {
        return StudentCertificate::class;
    }

    protected function fields(): array
    {
        return ['title', 'issuing_organization', 'issue_date', 'expiry_date', 'credential_url'];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|max:150',
            'issuing_organization' => 'required|max:150',
            'issue_date' => 'required|date',
            'expiry_date' => 'date',
            'credential_url' => 'url',
        ];
    }

    protected function handleUpload(Request $request): array
    {
        $file = $request->file('attachment');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        try {
            $path = (new FileUploadService())->upload($file, 'document');
            return ['attachment_path' => $path];
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            return [];
        }
    }
}
