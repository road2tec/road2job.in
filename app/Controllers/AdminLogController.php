<?php

namespace App\Controllers;

use App\Models\AuditLog;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminLogController extends Controller
{
    protected const RETENTION_DAYS = 30;
    protected const TAIL_LINES = 500;

    public function index(Request $request): void
    {
        $files = glob(base_path('storage/logs/app-*.log')) ?: [];
        rsort($files);

        $availableDates = array_map(fn ($path) => str_replace('app-', '', basename($path, '.log')), $files);

        $selectedDate = (string) $request->input('date', $availableDates[0] ?? date('Y-m-d'));
        $selectedDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate) ? $selectedDate : date('Y-m-d');

        $logPath = base_path("storage/logs/app-{$selectedDate}.log");
        $lines = [];

        if (is_file($logPath)) {
            $allLines = file($logPath, FILE_IGNORE_NEW_LINES);
            $lines = array_slice($allLines, -self::TAIL_LINES);
        }

        $this->view('dashboard/admin/logs', [
            'user' => Session::get('_user'),
            'availableDates' => $availableDates,
            'selectedDate' => $selectedDate,
            'lines' => array_reverse($lines),
            'retentionDays' => self::RETENTION_DAYS,
        ], 'admin');
    }

    public function cleanup(Request $request): void
    {
        $files = glob(base_path('storage/logs/app-*.log')) ?: [];
        $cutoff = strtotime('-' . self::RETENTION_DAYS . ' days');
        $deleted = 0;

        foreach ($files as $path) {
            $date = str_replace('app-', '', basename($path, '.log'));
            $timestamp = strtotime($date);

            if ($timestamp !== false && $timestamp < $cutoff) {
                unlink($path);
                $deleted++;
            }
        }

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_log_cleanup', "Deleted {$deleted} log file(s) older than " . self::RETENTION_DAYS . ' days', $request->ip());

        Session::flash('success', "Deleted {$deleted} old log file(s).");
        $this->redirect('/admin/logs');
    }
}
