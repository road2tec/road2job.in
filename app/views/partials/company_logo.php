<?php
/**
 * Shared company-logo renderer. Params: $logoPath (nullable), $name
 * (for alt text), $size in px (default 52), $shape 'rounded'|'circle'.
 * Consolidates 3 previously-divergent logo conventions
 * (jobs_public.php/job_show.php/company_show.php each had their own
 * size/shape/fallback) into one reusable partial.
 */
$logoSize = $size ?? 52;
$logoShape = $shape ?? 'rounded';
$logoShapeClass = $logoShape === 'circle' ? 'rounded-circle' : 'rounded';
?>
<?php if (!empty($logoPath)): ?>
    <img src="<?= url($logoPath) ?>" alt="<?= e($name ?? '') ?>" class="border <?= $logoShapeClass ?>" loading="lazy"
         style="width:<?= (int) $logoSize ?>px;height:<?= (int) $logoSize ?>px;object-fit:cover;flex-shrink:0;">
<?php else: ?>
    <div class="border <?= $logoShapeClass ?> d-flex align-items-center justify-content-center text-muted bg-body-tertiary"
         style="width:<?= (int) $logoSize ?>px;height:<?= (int) $logoSize ?>px;flex-shrink:0;">
        <i class="bi bi-building"></i>
    </div>
<?php endif; ?>
