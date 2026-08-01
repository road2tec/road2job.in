<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\JobPosting;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminJobController extends Controller
{
    protected const STATUSES = ['draft', 'published', 'closed'];

    public function index(Request $request): void
    {
        $filters = [
            'status' => (string) $request->input('status', ''),
            'keyword' => trim((string) $request->input('keyword', '')),
        ];
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $result = JobPosting::adminListing($filters, $page, $perPage);

        $this->view('dashboard/admin/jobs', [
            'user' => Session::get('_user'),
            'jobs' => $result['items'],
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
            $this->redirect('/admin/jobs');
            return;
        }

        JobPosting::update((int) $id, ['status' => $status]);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_job_status_update', "Set job posting #{$id} status to {$status}", $request->ip());

        Session::flash('success', 'Job status updated.');
        $this->redirect('/admin/jobs');
    }

    public function destroy(Request $request, string $id): void
    {
        JobPosting::delete((int) $id);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_job_delete', "Deleted job posting #{$id}", $request->ip());

        Session::flash('success', 'Job posting deleted.');
        $this->redirect('/admin/jobs');
    }
}
