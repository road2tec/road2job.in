<?php

namespace App\Controllers;

use App\Models\AuditLog;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $result = AuditLog::adminListing($keyword !== '' ? $keyword : null, $page, $perPage);

        $this->view('dashboard/admin/audit_logs', [
            'user' => Session::get('_user'),
            'logs' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'keyword' => $keyword,
        ], 'admin');
    }
}
