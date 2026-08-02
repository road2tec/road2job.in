<?php
$roleLabel = $user['role_label'] ?? 'Student';
$roleSlug = $user['role_slug'] ?? null;

if ($roleSlug === 'mentor') {
    $navItems = [
        ['label' => 'Overview', 'href' => '/dashboard', 'icon' => 'bi-grid'],
        ['label' => 'Mentor Profile', 'href' => '/dashboard/mentor', 'icon' => 'bi-person-badge'],
        ['label' => 'Requests', 'href' => '/dashboard/mentor/requests', 'icon' => 'bi-person-check'],
        ['label' => 'Community', 'href' => '/community', 'icon' => 'bi-people'],
        ['label' => 'Events', 'href' => '/dashboard/events', 'icon' => 'bi-calendar-event'],
        ['label' => 'Security', 'href' => '/account/security', 'icon' => 'bi-shield-lock'],
    ];
} else {
    $navItems = [
        ['label' => 'Overview', 'href' => '/dashboard', 'icon' => 'bi-grid'],
        ['label' => 'Profile', 'href' => '/dashboard/profile', 'icon' => 'bi-person'],
        ['label' => 'Resume', 'href' => '/dashboard/resume', 'icon' => 'bi-file-earmark-text'],
        ['label' => 'Applications', 'href' => '/dashboard/applications', 'icon' => 'bi-send-check'],
        ['label' => 'Chat', 'href' => '/dashboard/chat', 'icon' => 'bi-chat-dots'],
        ['label' => 'Interviews', 'href' => '/dashboard/my-interviews', 'icon' => 'bi-camera-video'],
        ['label' => 'Career Services', 'href' => '/dashboard/career-services', 'icon' => 'bi-briefcase'],
        ['label' => 'Analytics', 'href' => '/dashboard/analytics', 'icon' => 'bi-bar-chart'],
        ['label' => 'Assessments', 'href' => '/dashboard/assessments', 'icon' => 'bi-clipboard-check'],
        ['label' => 'Institute Updates', 'href' => '/dashboard/institute-updates', 'icon' => 'bi-mortarboard'],
        ['label' => 'Saved Jobs', 'href' => '/dashboard/saved-jobs', 'icon' => 'bi-bookmark-heart'],
        ['label' => 'Portfolio', 'href' => '/dashboard/portfolio', 'icon' => 'bi-globe'],
        ['label' => 'Research Hub', 'href' => '/dashboard/research', 'icon' => 'bi-journal-bookmark'],
        ['label' => 'Community', 'href' => '/community', 'icon' => 'bi-people'],
        ['label' => 'Events', 'href' => '/dashboard/events', 'icon' => 'bi-calendar-event'],
        ['label' => 'Settings', 'href' => '/dashboard/settings', 'icon' => 'bi-gear'],
        ['label' => 'Security', 'href' => '/account/security', 'icon' => 'bi-shield-lock'],
    ];
}

require base_path('app/views/partials/dashboard_shell.php');
