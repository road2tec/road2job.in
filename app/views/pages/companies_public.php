<?php
Core\View::partial('partials/page_header', [
    'title' => 'Companies',
    'subtitle' => 'Browse employer profiles and their open roles.',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($companies)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-building"></i></div>
                <h2 class="fw-semibold h5">No companies listed yet</h2>
                <p class="text-muted mb-0">Check back soon as employers join the platform.</p>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($companies as $company): ?>
                <a href="<?= url('/companies/' . $company['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body d-flex gap-3 align-items-start">
                        <?php if (!empty($company['logo_path'])): ?>
                            <img src="<?= url($company['logo_path']) ?>" alt="<?= e($company['name']) ?>" class="listing-card__logo" loading="lazy">
                        <?php else: ?>
                            <div class="listing-card__logo-fallback"><i class="bi bi-building"></i></div>
                        <?php endif; ?>
                        <div>
                            <h2 class="h6 fw-semibold mb-1">
                                <?= e($company['name']) ?>
                                <?php if ($company['verification_status'] === 'verified'): ?>
                                    <i class="bi bi-patch-check-fill text-primary small" title="Verified"></i>
                                <?php endif; ?>
                            </h2>
                            <?php if (!empty($company['headquarters_location'])): ?><div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($company['headquarters_location']) ?></div><?php endif; ?>
                            <div class="small text-muted"><?= (int) $company['published_job_count'] ?> open role<?= (int) $company['published_job_count'] === 1 ? '' : 's' ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
