<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminSettingController extends Controller
{
    protected const KEYS = ['site_name', 'support_email'];

    public function index(Request $request): void
    {
        $this->view('dashboard/admin/settings', [
            'user' => Session::get('_user'),
            'settings' => Setting::all(),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        Setting::set('site_name', trim((string) $request->input('site_name', '')));
        Setting::set('support_email', trim((string) $request->input('support_email', '')));

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_settings_update', 'Updated platform settings', $request->ip());

        Session::flash('success', 'Settings updated.');
        $this->redirect('/admin/settings');
    }
}
