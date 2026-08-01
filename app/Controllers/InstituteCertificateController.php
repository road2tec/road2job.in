<?php

namespace App\Controllers;

use App\Models\InstituteCertificate;
use App\Services\FileUploadService;
use Core\Request;
use Core\Session;

class InstituteCertificateController extends InstituteSubResourceController
{
    protected function modelClass(): string
    {
        return InstituteCertificate::class;
    }

    protected function fields(): array
    {
        return ['title', 'issuing_body', 'issued_year'];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|min:2|max:150',
            'issued_year' => 'numeric',
        ];
    }

    protected function handleUpload(Request $request): array
    {
        $file = $request->file('document');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        try {
            $path = (new FileUploadService())->upload($file, 'document');
            return ['document_path' => $path];
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            return [];
        }
    }
}
