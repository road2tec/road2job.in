<?php

namespace App\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use Core\Controller;
use Core\Request;
use Core\Session;

class AccountController extends Controller
{
    public function security(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $user = User::find($sessionUser['id']);

        $currentRecord = UserSession::findBySessionId(session_id());
        $currentSessionRecordId = $currentRecord['id'] ?? null;

        $sessions = UserSession::activeForUser($sessionUser['id']);
        $loginHistory = LoginAttempt::historyForIdentifiers([$user['email'], $user['phone']], 20);

        $roles = config('roles');
        $layout = $roles[$sessionUser['role_slug']]['layout'] ?? 'student';

        $this->view('account/security', [
            'user' => $sessionUser,
            'sessions' => $sessions,
            'currentSessionRecordId' => $currentSessionRecordId,
            'loginHistory' => $loginHistory,
        ], $layout);
    }

    public function revokeSession(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $record = UserSession::find((int) $id);

        if ($record !== null && (int) $record['user_id'] === (int) $sessionUser['id']) {
            UserSession::close((int) $id);
            Session::flash('success', 'Device signed out.');
        }

        $this->redirect('/account/security');
    }

    public function revokeAllOtherSessions(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $currentRecord = UserSession::findBySessionId(session_id());

        UserSession::closeAllForUserExcept((int) $sessionUser['id'], $currentRecord['id'] ?? null);

        Session::flash('success', 'All other devices have been signed out.');
        $this->redirect('/account/security');
    }
}
