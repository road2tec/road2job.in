<?php
$isOwnerView = $isOwnerView ?? true;
$templateLabels = [
    'ats' => 'ATS Classic',
    'professional' => 'Modern Professional',
    'creative' => 'Fresher / Student',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resume - <?= e($account['full_name'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
    <style>
        body { background: #eee; }
        /* The resume sheet represents a printed page - it must always
           read like paper+ink regardless of the site's dark mode toggle
           (which still applies normally to the toolbar above it), so its
           own design-token values are pinned to their light-mode values
           rather than inheriting whatever [data-bs-theme] is active.
           Capped at real A4 width (210mm) with print-realistic 15mm
           margins on desktop - what you see on screen is what prints, not
           an approximation of it. max-width (not a fixed width) so it can
           still shrink to fit on mobile/tablet, where a life-size A4
           preview wouldn't fit anyway. */
        .resume-sheet {
            --r2j-ink: #0a1f47;
            --r2j-text-muted: #64748b;
            --r2j-border: rgba(15, 23, 42, .08);
            --r2j-bg-subtle: #f8fafc;
            --bs-body-color: #1a1a1a;
            color-scheme: light;
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            margin: 2rem auto;
            box-sizing: border-box;
            background: #fff;
            color: #1a1a1a;
            padding: 15mm;
            box-shadow: 0 0 20px rgba(0,0,0,.08);
            font-size: 10.5pt;
            line-height: 1.45;
        }
        .no-print { width: 100%; max-width: 210mm; margin: 1rem auto 0; padding: 0 .5rem; box-sizing: border-box; }
        .resume-heading {
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: .2rem;
            margin-bottom: .5rem;
        }
        .resume-heading-accent { color: #d97a00; border-bottom-color: #f5900a; }
        /* Keeps a section heading glued to its first entry, and each entry
           glued together, across a page break - never split a heading from
           an empty rest-of-page, or a job title from its own dates/company. */
        .resume-section, .resume-entry { break-inside: avoid; }

        /* ATS Classic only - a distinct corporate-blue color scheme matched
           to a specific reference resume the user provided, kept under its
           own class names (ats-*) so Modern Professional/Fresher-Student
           are completely unaffected. */
        .ats-header { border-bottom: 2px solid #1f4e79; padding-bottom: .4rem; }
        .ats-name { font-size: 1.9rem; font-weight: 600; color: #1f4e79; letter-spacing: .01em; }
        .ats-heading {
            font-size: 1rem;
            font-weight: 700;
            color: #1f4e79;
            margin-top: 1rem;
            margin-bottom: .4rem;
        }
        .resume-section:first-of-type .ats-heading { margin-top: 0; }
        .ats-subheading { font-weight: 600; color: #2e75b6; }
        .ats-bullets { padding-left: 1.1rem; }
        .ats-bullets li { margin-bottom: .15rem; }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .resume-sheet { box-shadow: none; margin: 0; width: auto; min-height: auto; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <?php if ($isOwnerView): ?>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <a href="<?= url('/dashboard/profile') ?>" class="btn btn-outline-secondary btn-sm">&larr; Back to profile</a>
                <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <div class="btn-group btn-group-sm" role="group">
                    <?php foreach ($templates as $templateOption): ?>
                        <a href="<?= url('/dashboard/resume?template=' . $templateOption) ?>"
                           class="btn <?= $activeTemplate === $templateOption ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <?= e($templateLabels[$templateOption] ?? ucfirst($templateOption)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php if ($activeTemplate !== $savedTemplate): ?>
                    <form method="post" action="<?= url('/dashboard/resume/template') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="template" value="<?= e($activeTemplate) ?>">
                        <button type="submit" class="btn btn-sm btn-success">Set as default</button>
                    </form>
                <?php else: ?>
                    <span class="badge text-bg-light border">Default template</span>
                <?php endif; ?>
            </div>

            <div class="card mb-2">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small">Resume completeness: <strong><?= (int) $score['percent'] ?>%</strong></span>
                    <span class="small text-muted">
                        <?php foreach ($score['items'] as $item): ?>
                            <span class="me-2"><i class="bi <?= $item['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i> <?= e($item['label']) ?></span>
                        <?php endforeach; ?>
                    </span>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-end mb-2">
                <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
            </div>
        <?php endif; ?>
    </div>

    <div class="resume-sheet">
        <?= $content ?>
    </div>
</body>
</html>
