<?php

namespace App\Controllers;

use App\Models\JobAlert;
use App\Models\MentorshipRequest;
use App\Models\MockInterviewSession;
use App\Services\ResumeCompiler;
use App\Services\ResumeScorer;
use Core\Controller;
use Core\Request;
use Core\Session;

class CareerServicesController extends Controller
{
    public function hub(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $this->view('dashboard/student/career_services', [
            'user' => $sessionUser,
            'resumeScore' => ResumeScorer::score(ResumeCompiler::compile($userId)),
            'mockSessions' => MockInterviewSession::forStudent($userId),
            'jobAlerts' => JobAlert::forStudent($userId),
            'serviceRequests' => MentorshipRequest::forStudent($userId),
        ], 'student');
    }
}
