<?php

namespace App\Controllers;

use App\Models\College;
use App\Models\CollegeCampusDrive;
use App\Models\CollegeDriveRegistration;
use Core\Controller;
use Core\Request;
use Core\Session;

class CollegeDriveRegistrationController extends Controller
{
    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $college = College::findByUserId((int) $sessionUser['id']);

        $this->view('dashboard/college/registrations', [
            'user' => $sessionUser,
            'requests' => $college !== null ? CollegeDriveRegistration::forCollege((int) $college['id']) : [],
        ], 'employer');
    }

    public function updateStatus(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $college = College::findByUserId((int) $sessionUser['id']);
        $record = CollegeDriveRegistration::find((int) $id);

        if ($record === null || $college === null || (int) $record['college_id'] !== (int) $college['id']) {
            Session::flash('error', 'That registration could not be found.');
            $this->redirect('/dashboard/college/registrations');
            return;
        }

        $status = $request->input('status');

        if (!in_array($status, ['pending', 'shortlisted', 'selected', 'rejected'], true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/dashboard/college/registrations');
            return;
        }

        CollegeDriveRegistration::update((int) $id, ['status' => $status]);

        Session::flash('success', 'Status updated.');
        $this->redirect('/dashboard/college/registrations');
    }

    /**
     * Student-side action reached from the public college page. Inline
     * auth/role check rather than route-group middleware, since the GET
     * pages around it are public.
     */
    public function register(Request $request, string $driveId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in as a student to register for this drive.');
            $this->redirect('/login');
            return;
        }

        if ($sessionUser['role_slug'] !== 'student') {
            Session::flash('error', 'Only student accounts can register for campus drives.');
            $this->redirect('/colleges');
            return;
        }

        $drive = CollegeCampusDrive::find((int) $driveId);

        if ($drive === null || $drive['status'] !== 'published') {
            Session::flash('error', 'That drive is not available.');
            $this->redirect('/colleges');
            return;
        }

        $studentId = (int) $sessionUser['id'];

        if (CollegeDriveRegistration::hasActiveRequest((int) $driveId, $studentId)) {
            Session::flash('error', 'You already have an active registration for this drive.');
            $this->redirect('/colleges/' . $drive['college_id']);
            return;
        }

        $message = trim((string) $request->input('message', ''));

        CollegeDriveRegistration::create(
            (int) $drive['college_id'],
            (int) $driveId,
            $studentId,
            $message !== '' ? $message : null
        );

        Session::flash('success', 'Registration submitted. The college will reach out to you.');
        $this->redirect('/colleges/' . $drive['college_id']);
    }
}
