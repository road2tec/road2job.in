<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\College;
use App\Models\Institute;
use App\Models\InstitutePlacement;
use App\Models\InstituteUpdate;
use App\Models\JobApplication;
use App\Services\InstituteRankingScorer;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminDirectoryController extends Controller
{
    protected const PER_PAGE = 20;

    public function institutes(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $page = max(1, (int) $request->input('page', 1));
        $result = Institute::adminListing($keyword, $page, self::PER_PAGE);

        $this->view('dashboard/admin/directory_institutes', [
            'user' => Session::get('_user'),
            'institutes' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'keyword' => $keyword,
        ], 'admin');
    }

    /**
     * status: active|deactivated. A deactivated institute is excluded from
     * public discovery/Top/Trending immediately (PageController checks the
     * flag directly; Institute::publicListing()/topRanked()/trending() all
     * filter on status='active').
     */
    public function updateInstituteStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');

        if (!in_array($status, ['active', 'deactivated'], true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/admin/institutes');
            return;
        }

        Institute::update((int) $id, ['status' => $status]);
        InstituteRankingScorer::recompute((int) $id);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_institute_status_update', "Set institute #{$id} status to {$status}", $request->ip());

        Session::flash('success', 'Institute status updated.');
        $this->redirect('/admin/institutes');
    }

    /**
     * Institute owners never set this themselves - admin-only, matching the
     * brief's "Do not give institute owners control over verification."
     */
    public function updateInstituteVerification(Request $request, string $id): void
    {
        $status = (string) $request->input('verification_status', '');

        if (!in_array($status, ['unverified', 'verified'], true)) {
            Session::flash('error', 'Invalid verification status.');
            $this->redirect('/admin/institutes');
            return;
        }

        Institute::update((int) $id, ['verification_status' => $status]);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_institute_verification_update', "Set institute #{$id} verification to {$status}", $request->ip());

        Session::flash('success', 'Verification status updated.');
        $this->redirect('/admin/institutes');
    }

    public function updatePlacementStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');
        $placement = InstitutePlacement::find((int) $id);

        if ($placement === null || !in_array($status, ['active', 'deactivated'], true)) {
            Session::flash('error', 'That placement could not be found.');
            $this->redirect('/admin/institutes');
            return;
        }

        InstitutePlacement::setStatus((int) $id, $status);
        InstituteRankingScorer::recompute((int) $placement['institute_id']);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_placement_status_update', "Set placement #{$id} status to {$status}", $request->ip());

        Session::flash('success', 'Placement updated.');
        $this->redirect('/admin/institutes');
    }

    public function updateUpdateStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');
        $update = InstituteUpdate::find((int) $id);

        if ($update === null || !in_array($status, ['active', 'deactivated'], true)) {
            Session::flash('error', 'That update could not be found.');
            $this->redirect('/admin/institutes');
            return;
        }

        InstituteUpdate::setStatus((int) $id, $status);
        InstituteRankingScorer::recompute((int) $update['institute_id']);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_institute_update_status_update', "Set institute update #{$id} status to {$status}", $request->ip());

        Session::flash('success', 'Update moderated.');
        $this->redirect('/admin/institutes');
    }

    public function colleges(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $page = max(1, (int) $request->input('page', 1));
        $result = College::adminListing($keyword, $page, self::PER_PAGE);

        $this->view('dashboard/admin/directory_colleges', [
            'user' => Session::get('_user'),
            'colleges' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'keyword' => $keyword,
        ], 'admin');
    }

    public function applications(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $page = max(1, (int) $request->input('page', 1));
        $result = JobApplication::adminListing($keyword, $page, self::PER_PAGE);

        $this->view('dashboard/admin/directory_applications', [
            'user' => Session::get('_user'),
            'applications' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'keyword' => $keyword,
        ], 'admin');
    }
}
