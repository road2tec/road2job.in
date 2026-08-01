<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminCompanyController extends Controller
{
    protected const STATUSES = ['unverified', 'pending', 'verified', 'rejected'];

    public function index(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $result = Company::adminListing($keyword, $page, $perPage);

        $this->view('dashboard/admin/companies', [
            'user' => Session::get('_user'),
            'companies' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'keyword' => $keyword,
        ], 'admin');
    }

    public function updateVerification(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');

        if (!in_array($status, self::STATUSES, true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/admin/companies');
            return;
        }

        Company::setVerificationStatus((int) $id, $status);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_company_verification_update', "Set company #{$id} verification to {$status}", $request->ip());

        Session::flash('success', 'Verification status updated.');
        $this->redirect('/admin/companies');
    }
}
