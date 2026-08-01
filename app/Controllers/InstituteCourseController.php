<?php

namespace App\Controllers;

use App\Models\Institute;
use App\Models\InstituteCourse;
use Core\Request;
use Core\Session;

class InstituteCourseController extends InstituteSubResourceController
{
    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);

        $this->view('dashboard/institute/courses', [
            'user' => $sessionUser,
            'institute' => $institute,
            'courses' => $institute !== null ? InstituteCourse::forInstitute((int) $institute['id']) : [],
        ], 'employer');
    }

    protected function redirectTarget(): string
    {
        return '/dashboard/institute/courses';
    }

    protected function modelClass(): string
    {
        return InstituteCourse::class;
    }

    protected function fields(): array
    {
        return ['title', 'description', 'duration', 'mode', 'format', 'start_date', 'end_date', 'fee', 'syllabus_highlights', 'status'];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|min:2|max:150',
            'mode' => 'required|in:online,offline,hybrid',
            'format' => 'required|in:course,bootcamp,workshop',
            'status' => 'required|in:draft,published,closed',
            'fee' => 'numeric',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
