<?php

namespace App\Controllers;

use App\Models\StudentProfile;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Session;

class SettingsController extends Controller
{
    public function show(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $this->view('dashboard/settings', [
            'user' => $sessionUser,
            'account' => User::find($userId),
            'profile' => StudentProfile::findByUserId($userId),
        ], 'student');
    }

    public function updatePassword(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');
        $confirmation = (string) $request->input('new_password_confirmation', '');

        $account = User::find($userId);

        if ($account === null || !password_verify($currentPassword, $account['password_hash'])) {
            Session::flash('error', 'Your current password is incorrect.');
            $this->redirect('/dashboard/settings');
            return;
        }

        if (strlen($newPassword) < 8 || $newPassword !== $confirmation) {
            Session::flash('error', 'New password must be at least 8 characters and match confirmation.');
            $this->redirect('/dashboard/settings');
            return;
        }

        User::update($userId, ['password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)]);

        Session::flash('success', 'Password updated.');
        $this->redirect('/dashboard/settings');
    }

    public function updatePreferences(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $visibility = $request->input('profile_visibility') === 'public' ? 'public' : 'private';
        $username = strtolower(trim((string) $request->input('username', '')));

        if ($username !== '') {
            if (!preg_match('/^[a-z0-9-]{3,50}$/', $username)) {
                Session::flash('error', 'Username can only contain lowercase letters, numbers and hyphens (3-50 characters).');
                $this->redirect('/dashboard/settings');
                return;
            }

            $existing = User::findByUsername($username);

            if ($existing !== null && (int) $existing['id'] !== $userId) {
                Session::flash('error', 'That username is already taken.');
                $this->redirect('/dashboard/settings');
                return;
            }

            User::updateUsername($userId, $username);
        }

        StudentProfile::saveForUser($userId, [
            'profile_visibility' => $visibility,
            'email_notifications' => $request->input('email_notifications') ? 1 : 0,
            'sms_notifications' => $request->input('sms_notifications') ? 1 : 0,
        ]);

        Session::flash('success', 'Preferences updated.');
        $this->redirect('/dashboard/settings');
    }
}
