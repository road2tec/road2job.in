<?php

namespace App\Controllers;

use App\Models\JobPosting;
use App\Models\SavedJob;
use Core\Controller;
use Core\Request;
use Core\Session;

class SavedJobController extends Controller
{
    /**
     * Student-side action reached from the public job page. Inline
     * auth/role check rather than route-group middleware, since the GET
     * pages around it are public.
     */
    public function toggle(Request $request, string $jobPostingId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in as a student to save jobs.');
            $this->redirect('/login');
            return;
        }

        if ($sessionUser['role_slug'] !== 'student') {
            Session::flash('error', 'Only student accounts can save jobs.');
            $this->redirect('/jobs');
            return;
        }

        $job = JobPosting::findPublished((int) $jobPostingId);

        if ($job === null) {
            Session::flash('error', 'That job is not available.');
            $this->redirect('/jobs');
            return;
        }

        $studentId = (int) $sessionUser['id'];

        if (SavedJob::isSaved($studentId, (int) $jobPostingId)) {
            SavedJob::unsave($studentId, (int) $jobPostingId);
            Session::flash('success', 'Removed from saved jobs.');
        } else {
            SavedJob::save($studentId, (int) $jobPostingId);
            Session::flash('success', 'Saved!');
        }

        $this->redirect('/jobs/' . $jobPostingId);
    }

    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $this->view('dashboard/student/saved_jobs', [
            'user' => $sessionUser,
            'savedJobs' => SavedJob::forStudent((int) $sessionUser['id']),
        ], 'student');
    }
}
