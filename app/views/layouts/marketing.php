<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php Core\View::partial('partials/seo_meta', ['meta' => $meta ?? []]); ?>
    <?php Core\View::partial('partials/favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/marketing.css') ?>" rel="stylesheet">
    <?php foreach (($extraStyles ?? []) as $extraCssFile): ?>
        <link href="<?= asset('css/' . $extraCssFile) ?>" rel="stylesheet">
    <?php endforeach; ?>
    <script src="<?= asset('js/theme.js') ?>"></script>
</head>
<body>
    <a href="#main-content" class="visually-hidden-focusable">Skip to content</a>
    <?php Core\View::partial('partials/site_nav'); ?>

    <main id="main-content">
        <div class="container pt-4">
            <?php Core\View::partial('partials/flash'); ?>
        </div>

        <?= $content ?>
    </main>

    <?php Core\View::partial('partials/site_footer'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/scroll-reveal.js') ?>"></script>
    <script src="<?= asset('js/auth-forms.js') ?>"></script>
    <?php foreach (($extraScripts ?? []) as $extraJsFile): ?>
        <script src="<?= asset('js/' . $extraJsFile) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
