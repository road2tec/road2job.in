<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminNotificationController extends Controller
{
    protected const ROLES = ['student', 'employer', 'recruiter', 'institute', 'college', 'mentor'];

    public function index(Request $request): void
    {
        $this->view('dashboard/admin/notifications', [
            'user' => Session::get('_user'),
            'roles' => self::ROLES,
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $role = (string) $request->input('role', '');
        $title = trim((string) $request->input('title', ''));
        $message = trim((string) $request->input('message', ''));

        if ($title === '' || $message === '') {
            Session::flash('error', 'Please provide both a title and a message.');
            $this->redirect('/admin/notifications');
            return;
        }

        $filters = in_array($role, self::ROLES, true) ? ['role' => $role] : [];
        $recipients = User::adminListing($filters);

        foreach ($recipients as $recipient) {
            Notification::push((int) $recipient['id'], $title, $message, 'admin_broadcast');
        }

        $actor = Session::get('_user');
        $audience = $role !== '' ? $role : 'all users';
        AuditLog::record((int) $actor['id'], 'admin_notification_broadcast', "Broadcast '{$title}' to {$audience} (" . count($recipients) . ' recipients)', $request->ip());

        Session::flash('success', 'Notification sent to ' . count($recipients) . ' user(s).');
        $this->redirect('/admin/notifications');
    }
}
