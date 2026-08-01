<?php

namespace App\Controllers;

use App\Models\CollegeAlumnus;
use App\Services\FileUploadService;
use Core\Request;
use Core\Session;

class CollegeAlumniController extends CollegeSubResourceController
{
    protected function modelClass(): string
    {
        return CollegeAlumnus::class;
    }

    protected function fields(): array
    {
        return ['name', 'batch_year', 'department', 'current_position', 'current_company', 'testimonial'];
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:150',
            'batch_year' => 'numeric',
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
