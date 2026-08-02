<?php

namespace App\Controllers;

use App\Models\InstituteUpdate;
use Core\Controller;
use Core\Request;
use Core\Session;

/**
 * Student-facing aggregated feed of institute announcements/placements/
 * events across all active institutes. Replaces the old enrollment-based
 * "My Learning" page - institutes pay Road2Job for promotion, not course
 * sales, so students just see what institutes are posting, no enroll action.
 */
class InstituteUpdateFeedController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('dashboard/student/institute_updates', [
            'user' => Session::get('_user'),
            'updates' => InstituteUpdate::recentAcrossInstitutes(30),
        ], 'student');
    }
}
