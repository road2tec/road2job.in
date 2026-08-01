<h1 class="h4 mb-1">Welcome back, <?= e($user['full_name']) ?></h1>
<p class="text-muted mb-4">You're signed in as <strong><?= e($user['role_label']) ?></strong>.</p>

<div class="row g-3">
    <?php
    $quickLinks = [
        ['title' => 'Users', 'text' => 'Search, filter and manage account status.', 'icon' => 'bi-people', 'href' => '/admin/users'],
        ['title' => 'Companies', 'text' => 'Review and approve employer verification.', 'icon' => 'bi-building', 'href' => '/admin/companies'],
        ['title' => 'Jobs', 'text' => 'Moderate job postings platform-wide.', 'icon' => 'bi-briefcase', 'href' => '/admin/jobs'],
        ['title' => 'Events', 'text' => 'Moderate hosted events.', 'icon' => 'bi-calendar-event', 'href' => '/admin/events'],
        ['title' => 'Blog', 'text' => 'Write and publish blog posts.', 'icon' => 'bi-journal-text', 'href' => '/admin/blog'],
        ['title' => 'Notifications', 'text' => 'Broadcast an announcement to users.', 'icon' => 'bi-bell', 'href' => '/admin/notifications'],
        ['title' => 'Reports', 'text' => 'Download platform data as CSV.', 'icon' => 'bi-download', 'href' => '/admin/reports'],
        ['title' => 'Analytics', 'text' => 'Platform-wide charts and real placement counts.', 'icon' => 'bi-bar-chart', 'href' => '/dashboard/analytics'],
        ['title' => 'Security', 'text' => 'Login activity and session control.', 'icon' => 'bi-shield-lock', 'href' => '/admin/security'],
        ['title' => 'Audit Logs', 'text' => 'Full trail of admin actions.', 'icon' => 'bi-list-check', 'href' => '/admin/audit-logs'],
        ['title' => 'Settings', 'text' => 'Site name, support email, maintenance mode.', 'icon' => 'bi-gear', 'href' => '/admin/settings'],
    ];
    ?>
    <?php foreach ($quickLinks as $link): ?>
        <div class="col-md-4">
            <a href="<?= url($link['href']) ?>" class="card h-100 text-decoration-none text-reset">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="bi <?= e($link['icon']) ?>"></i></div>
                    <h2 class="h6 fw-semibold mb-1"><?= e($link['title']) ?></h2>
                    <p class="text-muted small mb-0"><?= e($link['text']) ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
