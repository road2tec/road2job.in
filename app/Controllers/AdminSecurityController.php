<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserSession;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminSecurityController extends Controller
{
    public function index(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));

        $this->view('dashboard/admin/security', [
            'user' => Session::get('_user'),
            'recentActivity' => AuditLog::recent(100),
            'keyword' => $keyword,
            'searchResults' => $keyword !== '' ? User::adminListing(['keyword' => $keyword]) : [],
        ], 'admin');
    }

    public function revokeSessions(Request $request, string $id): void
    {
        UserSession::closeAllForUserExcept((int) $id, null);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_force_logout', "Revoked all sessions for user #{$id}", $request->ip());

        Session::flash('success', 'All sessions for that user have been revoked.');
        $this->redirect('/admin/security');
    }
}
