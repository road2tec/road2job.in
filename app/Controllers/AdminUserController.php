<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminUserController extends Controller
{
    protected const STATUSES = ['pending', 'active', 'suspended', 'banned'];

    public function index(Request $request): void
    {
        $filters = [
            'role' => (string) $request->input('role', ''),
            'status' => (string) $request->input('status', ''),
            'keyword' => trim((string) $request->input('keyword', '')),
        ];
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $result = User::adminListing($filters, $page, $perPage);

        $this->view('dashboard/admin/users', [
            'user' => Session::get('_user'),
            'users' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters,
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    public function updateStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');

        if (!in_array($status, self::STATUSES, true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/admin/users');
            return;
        }

        User::setStatus((int) $id, $status);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_user_status_update', "Set user #{$id} status to {$status}", $request->ip());

        Session::flash('success', 'User status updated.');
        $this->redirect('/admin/users');
    }
}
