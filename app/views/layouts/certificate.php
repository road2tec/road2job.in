<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <style>
        body { background: #eee; }
        /* Always reads like a printed certificate - pinned to light-mode
           ink regardless of the site's dark mode toggle, same reasoning
           as .resume-sheet in layouts/print.php. */
        .certificate-sheet {
            --r2j-ink: #0a1f47;
            --r2j-text-muted: #64748b;
            --r2j-border: rgba(15, 23, 42, .08);
            --r2j-bg-subtle: #f8fafc;
            --bs-body-color: #1a1a1a;
            color-scheme: light;
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            color: #1a1a1a;
            padding: 3rem;
            box-shadow: 0 0 20px rgba(0,0,0,.08);
            border: 10px solid rgba(245, 144, 10, .13);
        }
        .no-print { max-width: 800px; margin: 1rem auto 0; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .certificate-sheet { box-shadow: none; margin: 0; padding: 0; max-width: 100%; border: none; }
        }
    </style>
</head>
<body>
    <div class="no-print d-flex justify-content-between align-items-center">
        <a href="<?= url('/dashboard') ?>" class="btn btn-outline-secondary btn-sm">&larr; Back to dashboard</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
    </div>

    <div class="certificate-sheet">
        <?= $content ?>
    </div>
</body>
</html>
