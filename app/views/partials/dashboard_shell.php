<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($roleLabel) ?> Dashboard - <?= e(config('app.name')) ?></title>
    <?php Core\View::partial('partials/favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <script src="<?= asset('js/theme.js') ?>"></script>
</head>
<body>
    <a href="#main-content" class="visually-hidden-focusable">Skip to content</a>
    <nav class="navbar navbar-expand-lg dash-navbar sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <a class="navbar-brand fw-bold mb-0 d-flex align-items-center gap-2" href="<?= url('/dashboard') ?>">
                    <img src="<?= asset('img/logo-icon-64.png') ?>" alt="" width="28" height="28">
                    Road2Job
                </a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge text-bg-light border"><?= e($roleLabel) ?></span>

                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="themeToggle">
                    <label class="form-check-label small" for="themeToggle">Dark</label>
                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm position-relative" data-bs-toggle="dropdown" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <?php if (!empty($notifications)): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= count($notifications) ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notifications">
                        <?php if (empty($notifications)): ?>
                            <li><span class="dropdown-item-text text-muted small">No notifications yet.</span></li>
                        <?php else: ?>
                            <?php foreach ($notifications as $note): ?>
                                <li>
                                    <div class="dropdown-item-text">
                                        <div class="fw-semibold small"><?= e($note['title']) ?></div>
                                        <div class="text-muted small"><?= e($note['message']) ?></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2 border" data-bs-toggle="dropdown">
                        <span class="avatar-initials mb-0" style="margin-right:0;">
                            <?= e(initials($user['full_name'] ?? null)) ?>
                        </span>
                        <span class="d-none d-sm-inline"><?= e($user['full_name']) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="<?= url('/logout') ?>" method="post" class="px-3 py-1">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link btn-sm p-0">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
            <div class="offcanvas-header d-lg-none">
                <h2 class="offcanvas-title h6 mb-0" id="sidebarOffcanvasLabel">Menu</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <?php
                /**
                 * A nav item like "Institute Profile" (/dashboard/institute) sits
                 * at the same URL prefix as its own sibling sub-pages
                 * (/dashboard/institute/courses, /roadmaps, ...), so
                 * is_active_path()'s prefix-matching rule - needed so a nav item
                 * correctly stays active on its own deeper sub-pages - would
                 * otherwise mark both the section-root item AND the sibling item
                 * active at once. Resolve the ambiguity the standard way:
                 * whichever matching href is longest (most specific) wins, and
                 * only that single nav item gets highlighted.
                 */
                $activeHref = null;
                $activeHrefLength = -1;
                foreach ($navItems as $navItem) {
                    if (($navItem['type'] ?? null) === 'heading' || !is_active_path($navItem['href'])) {
                        continue;
                    }
                    if (strlen($navItem['href']) > $activeHrefLength) {
                        $activeHref = $navItem['href'];
                        $activeHrefLength = strlen($navItem['href']);
                    }
                }
                ?>
                <ul class="nav flex-column gap-1">
                    <?php foreach ($navItems as $item): ?>
                        <?php if (($item['type'] ?? null) === 'heading'): ?>
                            <li class="nav-heading"><?= e($item['label']) ?></li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $item['href'] === $activeHref ? 'active' : '' ?>" href="<?= url($item['href']) ?>">
                                    <i class="bi <?= e($item['icon'] ?? 'bi-dot') ?>"></i>
                                    <span><?= e($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <main id="main-content" class="flex-fill p-4">
            <?php if (!empty($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                        <?php foreach ($breadcrumbs as $i => $crumb): ?>
                            <?php if ($i === array_key_last($breadcrumbs) || empty($crumb['href'])): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= e($crumb['label']) ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?= url($crumb['href']) ?>"><?= e($crumb['label']) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
            <?php Core\View::partial('partials/flash'); ?>
            <?= $content ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/auth-forms.js') ?>"></script>
</body>
</html>
