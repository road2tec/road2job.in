<?php
$roleLabel = $user['role_label'] ?? 'Employer';
$roleSlug = $user['role_slug'] ?? null;

if ($roleSlug === 'employer') {
    $navItems = [
        ['label' => 'Overview', 'href' => '/dashboard', 'icon' => 'bi-grid'],
        ['label' => 'Company Profile', 'href' => '/dashboard/company', 'icon' => 'bi-building'],
        ['label' => 'Job Postings', 'href' => '/dashboard/jobs', 'icon' => 'bi-briefcase'],
        ['label' => 'Applicants', 'href' => '/dashboard/applicants', 'icon' => 'bi-people'],
        ['label' => 'Chat', 'href' => '/dashboard/chat', 'icon' => 'bi-chat-dots'],
        ['label' => 'Interviews', 'href' => '/dashboard/interviews', 'icon' => 'bi-camera-video'],
        ['label' => 'Events', 'href' => '/dashboard/events', 'icon' => 'bi-calendar-event'],
        ['label' => 'Analytics', 'href' => '/dashboard/analytics', 'icon' => 'bi-bar-chart'],
        ['label' => 'Security', 'href' => '/account/security', 'icon' => 'bi-shield-lock'],
    ];
} elseif ($roleSlug === 'institute') {
    $navItems = [
        ['label' => 'Overview', 'href' => '/dashboard', 'icon' => 'bi-grid'],
        ['label' => 'Institute Profile', 'href' => '/dashboard/institute', 'icon' => 'bi-easel'],
        ['label' => 'Courses', 'href' => '/dashboard/institute/courses', 'icon' => 'bi-mortarboard'],
        ['label' => 'Updates', 'href' => '/dashboard/institute/updates', 'icon' => 'bi-megaphone'],
        ['label' => 'Roadmaps', 'href' => '/dashboard/institute/roadmaps', 'icon' => 'bi-signpost-2'],
        ['label' => 'Events', 'href' => '/dashboard/events', 'icon' => 'bi-calendar-event'],
        ['label' => 'Analytics', 'href' => '/dashboard/analytics', 'icon' => 'bi-bar-chart'],
        ['label' => 'Security', 'href' => '/account/security', 'icon' => 'bi-shield-lock'],
    ];
} elseif ($roleSlug === 'college') {
    $navItems = [
        ['label' => 'Overview', 'href' => '/dashboard', 'icon' => 'bi-grid'],
        ['label' => 'College Profile', 'href' => '/dashboard/college', 'icon' => 'bi-bank'],
        ['label' => 'Campus Drives', 'href' => '/dashboard/college/drives', 'icon' => 'bi-briefcase'],
        ['label' => 'Registrations', 'href' => '/dashboard/college/registrations', 'icon' => 'bi-person-check'],
        ['label' => 'Events', 'href' => '/dashboard/events', 'icon' => 'bi-calendar-event'],
        ['label' => 'Analytics', 'href' => '/dashboard/analytics', 'icon' => 'bi-bar-chart'],
        ['label' => 'Security', 'href' => '/account/security', 'icon' => 'bi-shield-lock'],
    ];
} else {
    // recruiter - their own module isn't built yet, keep placeholder links.
    $navItems = [
        ['label' => 'Overview', 'href' => '/dashboard', 'icon' => 'bi-grid'],
        ['label' => 'Company Profile', 'href' => '/dashboard', 'icon' => 'bi-building'],
        ['label' => 'Job Postings', 'href' => '/dashboard', 'icon' => 'bi-briefcase'],
        ['label' => 'Applicants', 'href' => '/dashboard', 'icon' => 'bi-people'],
        ['label' => 'Security', 'href' => '/account/security', 'icon' => 'bi-shield-lock'],
    ];
}

require base_path('app/views/partials/dashboard_shell.php');
