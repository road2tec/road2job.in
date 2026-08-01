<?php

namespace App\Controllers;

use App\Models\InstitutePlacement;
use App\Services\FileUploadService;
use App\Services\InstituteRankingScorer;
use Core\Request;
use Core\Session;

class InstitutePlacementController extends InstituteSubResourceController
{
    protected function modelClass(): string
    {
        return InstitutePlacement::class;
    }

    protected function fields(): array
    {
        return ['student_name', 'company_name', 'job_role', 'placement_type', 'package_amount', 'placement_year', 'placement_date', 'course_name', 'description'];
    }

    protected function rules(): array
    {
        return [
            'student_name' => 'required|min:2|max:150',
            'company_name' => 'required|min:2|max:150',
            'package_amount' => 'numeric',
            'placement_year' => 'numeric',
            'placement_date' => 'date',
        ];
    }

    protected function handleUpload(Request $request): array
    {
        $file = $request->file('student_photo');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        try {
            $path = (new FileUploadService())->upload($file, 'avatar');
            return ['student_photo_path' => $path];
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            return [];
        }
    }

    protected function afterSave(array $institute): void
    {
        InstituteRankingScorer::recordEvent((int) $institute['id'], 'placement_added');
    }
}
