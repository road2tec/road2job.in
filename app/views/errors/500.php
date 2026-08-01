<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>500 - Something Went Wrong</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-body-tertiary">
    <div class="text-center" style="max-width: 700px;">
        <h1 class="display-3 fw-bold">500</h1>
        <p class="text-muted mb-4">Something went wrong on our end. Our team has been notified.</p>
        <a href="/" class="btn btn-primary">Go Home</a>

        <?php if (!empty($debug) && isset($e)): ?>
            <div class="alert alert-danger text-start mt-4">
                <strong><?= htmlspecialchars(get_class($e)) ?>:</strong> <?= htmlspecialchars($e->getMessage()) ?><br>
                <small><?= htmlspecialchars($e->getFile()) ?>:<?= (int) $e->getLine() ?></small>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
