<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\Event;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminEventController extends Controller
{
    public function index(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $result = Event::adminListing($keyword, $page, $perPage);

        $this->view('dashboard/admin/events', [
            'user' => Session::get('_user'),
            'events' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'keyword' => $keyword,
        ], 'admin');
    }

    public function destroy(Request $request, string $id): void
    {
        Event::delete((int) $id);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_event_delete', "Deleted event #{$id}", $request->ip());

        Session::flash('success', 'Event deleted.');
        $this->redirect('/admin/events');
    }
}
