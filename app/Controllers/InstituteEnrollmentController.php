<?php

namespace App\Controllers;

use App\Models\Institute;
use App\Models\InstituteCourse;
use App\Models\InstituteEnrollmentRequest;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class InstituteEnrollmentController extends Controller
{
    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);

        $this->view('dashboard/institute/enrollments', [
            'user' => $sessionUser,
            'requests' => $institute !== null ? InstituteEnrollmentRequest::forInstitute((int) $institute['id']) : [],
        ], 'employer');
    }

    public function updateStatus(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);
        $record = InstituteEnrollmentRequest::find((int) $id);

        if ($record === null || $institute === null || (int) $record['institute_id'] !== (int) $institute['id']) {
            Session::flash('error', 'That request could not be found.');
            $this->redirect('/dashboard/institute/enrollments');
            return;
        }

        $status = $request->input('status');

        if (!in_array($status, ['pending', 'contacted', 'enrolled', 'completed', 'declined'], true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/dashboard/institute/enrollments');
            return;
        }

        InstituteEnrollmentRequest::update((int) $id, ['status' => $status]);

        Session::flash('success', 'Status updated.');
        $this->redirect('/dashboard/institute/enrollments');
    }

    /**
     * Student-side action reached from the public institute page. Inline
     * auth/role check rather than route-group middleware, since the GET
     * pages around it are public.
     */
    public function request(Request $request, string $courseId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in as a student to request enrollment.');
            $this->redirect('/login');
            return;
        }

        if ($sessionUser['role_slug'] !== 'student') {
            Session::flash('error', 'Only student accounts can request enrollment.');
            $this->redirect('/institutes');
            return;
        }

        $course = InstituteCourse::find((int) $courseId);

        if ($course === null || $course['status'] !== 'published') {
            Session::flash('error', 'That course is not available.');
            $this->redirect('/institutes');
            return;
        }

        $studentId = (int) $sessionUser['id'];

        if (InstituteEnrollmentRequest::hasActiveRequest((int) $courseId, $studentId)) {
            Session::flash('error', 'You already have an active request for this course.');
            $this->redirect('/institutes/' . $course['institute_id']);
            return;
        }

        $message = trim((string) $request->input('message', ''));

        InstituteEnrollmentRequest::create(
            (int) $course['institute_id'],
            (int) $courseId,
            $studentId,
            $message !== '' ? $message : null
        );

        Session::flash('success', 'Enrollment request sent. The institute will reach out to you.');
        $this->redirect('/institutes/' . $course['institute_id']);
    }

    /**
     * Student-side course-completion certificate. Only reachable once the
     * institute has marked the enrollment 'completed' - same "computed view
     * over a real state, not a new persisted entity" pattern as Phase 13.
     */
    public function certificate(Request $request, string $courseId): void
    {
        $sessionUser = Session::get('_user');
        $studentId = (int) $sessionUser['id'];
        $enrollment = InstituteEnrollmentRequest::findEnrollment((int) $courseId, $studentId);

        if ($enrollment === null || $enrollment['status'] !== 'completed') {
            Response::abort(404);
            return;
        }

        $course = InstituteCourse::find((int) $courseId);
        $institute = $course !== null ? Institute::find((int) $course['institute_id']) : null;

        $this->view('dashboard/student/course_certificate', [
            'user' => $sessionUser,
            'course' => $course,
            'institute' => $institute,
            'enrollment' => $enrollment,
        ], 'certificate');
    }
}
