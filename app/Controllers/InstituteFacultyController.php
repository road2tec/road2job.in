<?php

namespace App\Controllers;

use App\Models\InstituteFaculty;
use App\Services\FileUploadService;
use Core\Request;
use Core\Session;

class InstituteFacultyController extends InstituteSubResourceController
{
    protected function modelClass(): string
    {
        return InstituteFaculty::class;
    }

    protected function fields(): array
    {
        return ['name', 'designation', 'bio', 'expertise'];
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:150',
        ];
    }

    protected function handleUpload(Request $request): array
    {
        $file = $request->file('photo');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        try {
            $path = (new FileUploadService())->upload($file, 'avatar');
            return ['photo_path' => $path];
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            return [];
        }
    }
}
