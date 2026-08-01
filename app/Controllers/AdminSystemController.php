<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Core\Controller;
use Core\Database;
use Core\Request;
use Core\Session;

class AdminSystemController extends Controller
{
    public function roles(Request $request): void
    {
        $roles = config('roles');
        $counts = User::countByRole();

        $rows = [];
        foreach ($roles as $slug => $meta) {
            $rows[] = [
                'slug' => $slug,
                'label' => $meta['label'],
                'self_registerable' => $meta['self_registerable'],
                'count' => $counts[$slug] ?? 0,
            ];
        }

        $this->view('dashboard/admin/roles', [
            'user' => Session::get('_user'),
            'roles' => $rows,
        ], 'admin');
    }

    public function index(Request $request): void
    {
        $sms = config('sms');
        $mail = config('mail');

        $smsStatus = [
            'Fast2SMS API key' => !empty($sms['fast2sms']['api_key']),
            'Fast2SMS sender ID' => !empty($sms['fast2sms']['sender_id']),
            'WhatsApp phone number ID' => !empty($sms['whatsapp']['phone_number_id']),
            'WhatsApp OTP template' => !empty($sms['whatsapp']['template_name']),
        ];

        $mailStatus = [
            'SMTP host' => !empty($mail['host']),
            'SMTP username' => !empty($mail['username']),
            'SMTP password' => !empty($mail['password']),
        ];

        $this->view('dashboard/admin/system', [
            'user' => Session::get('_user'),
            'smsStatus' => $smsStatus,
            'mailStatus' => $mailStatus,
            'otpChannel' => $sms['otp_channel'] ?? 'sms',
            'maintenanceMode' => Setting::get('maintenance_mode', '0') === '1',
        ], 'admin');
    }

    public function health(Request $request): void
    {
        $dbHealthy = true;
        $dbError = null;

        try {
            Database::connection()->query('SELECT 1');
        } catch (\Throwable $e) {
            $dbHealthy = false;
            $dbError = $e->getMessage();
        }

        $errorCount = 0;
        foreach ([date('Y-m-d'), date('Y-m-d', strtotime('-1 day'))] as $day) {
            $path = base_path("storage/logs/app-{$day}.log");

            if (is_file($path)) {
                $errorCount += substr_count(file_get_contents($path), '] ERROR:');
            }
        }

        $diskFree = disk_free_space(base_path());

        $this->view('dashboard/admin/health', [
            'user' => Session::get('_user'),
            'dbHealthy' => $dbHealthy,
            'dbError' => $dbError,
            'errorCount' => $errorCount,
            'phpVersion' => phpversion(),
            'memoryLimit' => ini_get('memory_limit'),
            'diskFreeGb' => $diskFree !== false ? round($diskFree / 1024 / 1024 / 1024, 1) : null,
        ], 'admin');
    }

    public function updateMaintenanceMode(Request $request): void
    {
        $enabled = $request->input('maintenance_mode') ? '1' : '0';
        Setting::set('maintenance_mode', $enabled);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_maintenance_mode_update', 'Set maintenance mode to ' . $enabled, $request->ip());

        Session::flash('success', $enabled === '1' ? 'Maintenance mode enabled.' : 'Maintenance mode disabled.');
        $this->redirect('/admin/system');
    }
}
