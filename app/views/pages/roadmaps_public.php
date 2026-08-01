<?php
Core\View::partial('partials/page_header', [
    'title' => 'Roadmaps',
    'subtitle' => 'Step-by-step learning roadmaps shared by training institutes.',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($roadmaps)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-signpost-2"></i></div>
                <h2 class="fw-semibold h5">No roadmaps shared yet</h2>
                <p class="text-muted mb-0">Training institutes haven't published a roadmap yet - check back soon.</p>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($roadmaps as $roadmap): ?>
                <a href="<?= url('/roadmaps/' . $roadmap['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-1"><?= e($roadmap['title']) ?></h2>
                        <?php if (!empty($roadmap['description'])): ?><p class="small text-muted mb-1"><?= e($roadmap['description']) ?></p><?php endif; ?>
                        <div class="small text-muted">by <?= e($roadmap['institute_name']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
