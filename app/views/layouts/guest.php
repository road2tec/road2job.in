<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(config('app.name')) ?></title>
    <?php Core\View::partial('partials/favicon'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <script src="<?= asset('js/theme.js') ?>"></script>
</head>
<body class="bg-body-tertiary">
    <a href="#main-content" class="visually-hidden-focusable">Skip to content</a>
    <nav class="navbar navbar-expand-lg bg-body border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold mb-0 d-flex align-items-center gap-2" href="<?= url('/') ?>">
                <img src="<?= asset('img/logo-icon-64.png') ?>" alt="" width="30" height="30">
                Road2Job
            </a>
            <div class="d-flex align-items-center gap-2">
                <div class="form-check form-switch mb-0 me-1">
                    <input class="form-check-input" type="checkbox" id="themeToggle">
                    <label class="form-check-label small" for="themeToggle">Dark</label>
                </div>
                <a href="<?= url('/login') ?>" class="btn btn-outline-primary btn-sm">Login</a>
                <a href="<?= url('/register') ?>" class="btn btn-primary btn-sm">Get Started</a>
            </div>
        </div>
    </nav>

    <main id="main-content" class="container py-5">
        <div class="mx-auto" style="max-width: 720px;">
            <?php Core\View::partial('partials/flash'); ?>
        </div>
        <?= $content ?>
    </main>

    <footer class="border-top py-4 text-center text-muted small">
        &copy; <?= date('Y') ?> Road2Job. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/auth-forms.js') ?>"></script>
</body>
</html>
