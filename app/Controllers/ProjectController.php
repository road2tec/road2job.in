<?php

namespace App\Controllers;

use App\Models\StudentProject;
use App\Services\FileUploadService;
use Core\Request;
use Core\Session;

class ProjectController extends StudentSubResourceController
{
    protected function modelClass(): string
    {
        return StudentProject::class;
    }

    protected function fields(): array
    {
        return ['title', 'role', 'project_type', 'description', 'project_url', 'start_date', 'end_date'];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|max:150',
            'role' => 'max:100',
            'project_type' => 'max:100',
            'description' => 'required',
            'project_url' => 'url',
            'start_date' => 'required|date',
            'end_date' => 'date',
        ];
    }

    protected function prepareData(Request $request, array $data): array
    {
        // Not in fields()/rules() (a free-form chip list, not a single
        // validated string) - read and join separately.
        $technologiesUsed = (array) $request->input('technologies_used', []);
        $technologiesUsed = array_values(array_filter(array_map('trim', $technologiesUsed), fn ($v) => $v !== ''));
        $data['technologies_used'] = !empty($technologiesUsed) ? implode(', ', $technologiesUsed) : null;

        $data['is_featured'] = $request->input('is_featured') ? 1 : 0;

        return $data;
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
