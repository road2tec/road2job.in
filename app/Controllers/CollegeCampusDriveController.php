<?php

namespace App\Controllers;

use App\Models\College;
use App\Models\CollegeCampusDrive;
use Core\Request;
use Core\Session;

class CollegeCampusDriveController extends CollegeSubResourceController
{
    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $college = College::findByUserId((int) $sessionUser['id']);

        $this->view('dashboard/college/drives', [
            'user' => $sessionUser,
            'college' => $college,
            'drives' => $college !== null ? CollegeCampusDrive::forCollege((int) $college['id']) : [],
        ], 'employer');
    }

    protected function redirectTarget(): string
    {
        return '/dashboard/college/drives';
    }

    protected function modelClass(): string
    {
        return CollegeCampusDrive::class;
    }

    protected function fields(): array
    {
        return ['company_name', 'drive_date', 'eligible_departments', 'min_cgpa', 'description', 'status'];
    }

    protected function rules(): array
    {
        return [
            'company_name' => 'required|min:2|max:150',
            'status' => 'required|in:draft,published,closed',
            'drive_date' => 'date',
            'min_cgpa' => 'numeric',
        ];
    }
}
