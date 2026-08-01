<?php

namespace App\Controllers;

use App\Models\JobAlert;
use Core\Controller;
use Core\Request;
use Core\Session;

class JobAlertController extends Controller
{
    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $this->view('dashboard/student/job_alerts', [
            'user' => $sessionUser,
            'alerts' => JobAlert::forStudent((int) $sessionUser['id']),
        ], 'student');
    }

    public function store(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'location' => trim((string) $request->input('location', '')),
            'type' => (string) $request->input('type', ''),
            'experience_level' => (string) $request->input('experience_level', ''),
            'is_remote' => $request->input('is_remote') ? 1 : 0,
        ];

        if ($filters['keyword'] === '' && $filters['location'] === '' && $filters['type'] === '' && $filters['experience_level'] === '' && !$filters['is_remote']) {
            Session::flash('error', 'Please set at least one filter for your alert.');
            $this->redirect('/dashboard/job-alerts');
            return;
        }

        JobAlert::create((int) $sessionUser['id'], $filters);

        Session::flash('success', 'Job alert saved.');
        $this->redirect('/dashboard/job-alerts');
    }

    public function destroy(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $alert = JobAlert::find((int) $id);

        if ($alert !== null && (int) $alert['student_id'] === (int) $sessionUser['id']) {
            JobAlert::delete((int) $id);
            Session::flash('success', 'Job alert removed.');
        }

        $this->redirect('/dashboard/job-alerts');
    }
}
