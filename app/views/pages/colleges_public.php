<?php
Core\View::partial('partials/page_header', [
    'title' => 'Colleges',
    'subtitle' => 'Browse college placement cells, campus drives and placement records.',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($colleges)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-bank"></i></div>
                <h2 class="fw-semibold h5">No colleges listed yet</h2>
                <p class="text-muted mb-0">Check back soon as colleges join the platform.</p>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($colleges as $college): ?>
                <a href="<?= url('/colleges/' . $college['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body d-flex gap-3 align-items-start">
                        <?php if (!empty($college['logo_path'])): ?>
                            <img src="<?= url($college['logo_path']) ?>" alt="<?= e($college['name']) ?>" class="listing-card__logo" loading="lazy">
                        <?php else: ?>
                            <div class="listing-card__logo-fallback"><i class="bi bi-bank"></i></div>
                        <?php endif; ?>
                        <div>
                            <h2 class="h6 fw-semibold mb-1"><?= e($college['name']) ?></h2>
                            <?php if (!empty($college['location'])): ?><div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($college['location']) ?></div><?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
