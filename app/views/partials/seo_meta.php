<?php $canonicalUrl = url(current_path()); ?>
<title><?= e($meta['title'] ?? config('app.name')) ?></title>
<meta name="description" content="<?= e($meta['description'] ?? '') ?>">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">
<meta property="og:title" content="<?= e($meta['title'] ?? config('app.name')) ?>">
<meta property="og:description" content="<?= e($meta['description'] ?? '') ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(config('app.name')) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<?php if (!empty($meta['image'])): ?>
<meta property="og:image" content="<?= e($meta['image']) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="<?= e($meta['image']) ?>">
<?php else: ?>
<meta name="twitter:card" content="summary">
<?php endif; ?>
<meta name="twitter:title" content="<?= e($meta['title'] ?? config('app.name')) ?>">
<meta name="twitter:description" content="<?= e($meta['description'] ?? '') ?>">
<meta name="robots" content="index, follow">
