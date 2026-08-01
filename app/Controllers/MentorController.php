<?php

namespace App\Controllers;

use App\Models\MentorProfile;
use App\Models\MentorshipRequest;
use App\Models\MockInterviewSession;
use App\Services\ResumeCompiler;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class MentorController extends Controller
{
    protected const SERVICE_TYPES = ['mentorship', 'resume-review', 'portfolio-review', 'career-counseling', 'mock-interview-feedback'];

    // ---- Public ----

    public function directory(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = MentorProfile::publicListing($page, $perPage);

        $this->view('pages/mentors_public', [
            'mentors' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'service' => $this->resolveServiceType($request),
            'mockSession' => $request->input('mock_session', ''),
            'meta' => [
                'title' => 'Mentors - Road2Job',
                'description' => 'Browse mentors offering career guidance to students on Road2Job.',
            ],
        ], 'marketing');
    }

    public function show(Request $request, string $id): void
    {
        $mentor = MentorProfile::findWithUser((int) $id);

        if ($mentor === null || empty($mentor['expertise'])) {
            Response::abort(404);
            return;
        }

        $sessionUser = Session::get('_user');
        $isStudent = $sessionUser !== null && $sessionUser['role_slug'] === 'student';
        $service = $this->resolveServiceType($request);
        $alreadyRequested = $isStudent && MentorshipRequest::hasActiveRequest((int) $id, (int) $sessionUser['id'], $service);

        $this->view('pages/mentor_show', [
            'mentor' => $mentor,
            'isStudent' => $isStudent,
            'alreadyRequested' => $alreadyRequested,
            'service' => $service,
            'mockSession' => $request->input('mock_session', ''),
            'meta' => [
                'title' => $mentor['full_name'] . ' - Mentor - Road2Job',
                'description' => 'Career mentorship from ' . $mentor['full_name'] . ' on Road2Job.',
            ],
        ], 'marketing');
    }

    /**
     * Student-side action reached from the public mentor page. Inline
     * auth/role check rather than route-group middleware, since the GET
     * pages around it are public.
     */
    public function request(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in as a student to request mentorship.');
            $this->redirect('/login');
            return;
        }

        if ($sessionUser['role_slug'] !== 'student') {
            Session::flash('error', 'Only student accounts can request mentorship.');
            $this->redirect('/mentors');
            return;
        }

        $mentor = MentorProfile::findWithUser((int) $id);

        if ($mentor === null) {
            Response::abort(404);
            return;
        }

        $studentId = (int) $sessionUser['id'];
        $serviceType = $this->resolveServiceType($request);

        if (MentorshipRequest::hasActiveRequest((int) $id, $studentId, $serviceType)) {
            Session::flash('error', 'You already have a pending request of this type with this mentor.');
            $this->redirect('/mentors/' . $id);
            return;
        }

        $resumeSnapshot = null;
        if (in_array($serviceType, ['resume-review', 'portfolio-review'], true)) {
            $resumeSnapshot = json_encode(ResumeCompiler::compile($studentId));
        }

        $mockInterviewSessionId = null;
        if ($serviceType === 'mock-interview-feedback') {
            $mockSessionId = (int) $request->input('mock_interview_session_id', 0);
            $mockSession = $mockSessionId > 0 ? MockInterviewSession::find($mockSessionId) : null;

            if ($mockSession === null || (int) $mockSession['student_id'] !== $studentId || $mockSession['status'] !== 'completed') {
                Session::flash('error', 'That mock interview session is not available for feedback.');
                $this->redirect('/mentors/' . $id);
                return;
            }

            $mockInterviewSessionId = $mockSessionId;
        }

        $message = trim((string) $request->input('message', ''));

        MentorshipRequest::create((int) $id, $studentId, $message !== '' ? $message : null, $serviceType, $resumeSnapshot, $mockInterviewSessionId);

        Session::flash('success', 'Request sent!');
        $this->redirect('/mentors/' . $id);
    }

    protected function resolveServiceType(Request $request): string
    {
        $service = (string) $request->input('service', 'mentorship');

        return in_array($service, self::SERVICE_TYPES, true) ? $service : 'mentorship';
    }

    // ---- Mentor dashboard ----

    public function profile(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $this->view('dashboard/mentor/profile', [
            'user' => $sessionUser,
            'mentor' => MentorProfile::findByUserId((int) $sessionUser['id']),
        ], 'student');
    }

    public function updateProfile(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $data = $request->only(['expertise', 'bio', 'availability_note']);

        $validator = Validator::make($data);
        $validator->validate([
            'expertise' => 'required|min:2|max:255',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/mentor');
            return;
        }

        MentorProfile::saveForUser($userId, $data);

        Session::flash('success', 'Mentor profile updated.');
        $this->redirect('/dashboard/mentor');
    }

    public function requests(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $mentor = MentorProfile::findByUserId((int) $sessionUser['id']);

        $this->view('dashboard/mentor/requests', [
            'user' => $sessionUser,
            'requests' => $mentor !== null ? MentorshipRequest::forMentor((int) $mentor['id']) : [],
        ], 'student');
    }

    public function updateRequestStatus(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $mentor = MentorProfile::findByUserId((int) $sessionUser['id']);
        $record = MentorshipRequest::find((int) $id);

        if ($record === null || $mentor === null || (int) $record['mentor_profile_id'] !== (int) $mentor['id']) {
            Session::flash('error', 'That request could not be found.');
            $this->redirect('/dashboard/mentor/requests');
            return;
        }

        $status = $request->input('status');

        if (!in_array($status, ['pending', 'accepted', 'declined'], true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/dashboard/mentor/requests');
            return;
        }

        $feedback = trim((string) $request->input('feedback', ''));

        MentorshipRequest::update((int) $id, [
            'status' => $status,
            'feedback' => $feedback !== '' ? $feedback : $record['feedback'],
        ]);

        Session::flash('success', 'Status updated.');
        $this->redirect('/dashboard/mentor/requests');
    }

    public function viewSnapshot(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $mentor = MentorProfile::findByUserId((int) $sessionUser['id']);
        $record = MentorshipRequest::find((int) $id);

        if ($record === null || $mentor === null || (int) $record['mentor_profile_id'] !== (int) $mentor['id'] || empty($record['resume_snapshot'])) {
            Response::abort(404);
            return;
        }

        $snapshot = json_decode((string) $record['resume_snapshot'], true) ?? [];

        $this->view('resume/templates/professional', array_merge($snapshot, [
            'isOwnerView' => false,
        ]), 'print');
    }
}
