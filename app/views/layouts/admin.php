<?php
$roleLabel = 'Admin';
$isSuperAdmin = ($user['role_slug'] ?? null) === 'super_admin';

$navItems = [
    ['label' => 'Overview', 'href' => '/dashboard', 'icon' => 'bi-grid'],

    ['type' => 'heading', 'label' => 'Platform'],
    ['label' => 'Users', 'href' => '/admin/users', 'icon' => 'bi-people'],
    ['label' => 'Companies', 'href' => '/admin/companies', 'icon' => 'bi-building'],
    ['label' => 'Institutes', 'href' => '/admin/institutes', 'icon' => 'bi-easel'],
    ['label' => 'Colleges', 'href' => '/admin/colleges', 'icon' => 'bi-bank'],
    ['label' => 'Jobs', 'href' => '/admin/jobs', 'icon' => 'bi-briefcase'],
    ['label' => 'Applications', 'href' => '/admin/applications', 'icon' => 'bi-send-check'],

    ['type' => 'heading', 'label' => 'Content'],
    ['label' => 'Events', 'href' => '/admin/events', 'icon' => 'bi-calendar-event'],
    ['label' => 'Blog', 'href' => '/admin/blog', 'icon' => 'bi-journal-text'],
    ['label' => 'Notifications', 'href' => '/admin/notifications', 'icon' => 'bi-bell'],

    ['type' => 'heading', 'label' => 'Insights'],
    ['label' => 'Reports', 'href' => '/admin/reports', 'icon' => 'bi-download'],
    ['label' => 'Analytics', 'href' => '/dashboard/analytics', 'icon' => 'bi-bar-chart'],
    ['label' => 'Revenue', 'href' => '/dashboard/revenue-analytics', 'icon' => 'bi-graph-up-arrow'],
    ['label' => 'Payments', 'href' => '/admin/payments', 'icon' => 'bi-credit-card'],
    ['label' => 'Advertisements', 'href' => '/admin/advertisements', 'icon' => 'bi-megaphone'],

    ['type' => 'heading', 'label' => 'Administration'],
    ['label' => 'Security', 'href' => '/admin/security', 'icon' => 'bi-shield-lock'],
    ['label' => 'Audit Logs', 'href' => '/admin/audit-logs', 'icon' => 'bi-list-check'],
    ['label' => 'Settings', 'href' => '/admin/settings', 'icon' => 'bi-gear'],
];

if ($isSuperAdmin) {
    $navItems[] = ['type' => 'heading', 'label' => 'Super Admin'];
    $navItems[] = ['label' => 'Roles', 'href' => '/admin/roles', 'icon' => 'bi-person-badge'];
    $navItems[] = ['label' => 'System', 'href' => '/admin/system', 'icon' => 'bi-hdd-network'];
    $navItems[] = ['label' => 'System Health', 'href' => '/admin/health', 'icon' => 'bi-activity'];
    $navItems[] = ['label' => 'Error Logs', 'href' => '/admin/logs', 'icon' => 'bi-file-text'];
    $navItems[] = ['label' => 'API Configuration', 'href' => '/admin/api-configuration', 'icon' => 'bi-plug'];
    $navItems[] = ['label' => 'Cron Jobs', 'href' => '/admin/cron-jobs', 'icon' => 'bi-clock-history'];
    $navItems[] = ['label' => 'Database Backup', 'href' => '/admin/database-backup', 'icon' => 'bi-database'];
    $navItems[] = ['label' => 'Fraud Detection', 'href' => '/admin/fraud-detection', 'icon' => 'bi-exclamation-triangle'];
}

$navItems[] = ['type' => 'heading', 'label' => 'Account'];
$navItems[] = ['label' => 'My Security', 'href' => '/account/security', 'icon' => 'bi-person-lock'];

require base_path('app/views/partials/dashboard_shell.php');
