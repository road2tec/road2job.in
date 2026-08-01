<div class="container py-5" style="max-width: 840px;">

    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-4 align-items-center">
            <?php if (!empty($company['logo_path'])): ?>
                <img src="<?= url($company['logo_path']) ?>" alt="<?= e($company['name']) ?>" class="rounded-circle border" loading="lazy" style="width:96px;height:96px;object-fit:cover;">
            <?php else: ?>
                <div class="rounded-circle d-flex align-items-center justify-content-center feature-icon" style="width:96px;height:96px;">
                    <i class="bi bi-building fs-2"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="h3 fw-bold mb-1 font-display">
                    <?= e($company['name']) ?>
                    <?php if ($company['verification_status'] === 'verified'): ?>
                        <span class="badge bg-primary"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
                    <?php endif; ?>
                </h1>
                <p class="small text-muted mb-0">
                    <?php if (!empty($company['industry'])): ?><?= e($company['industry']) ?><?php endif; ?>
                    <?php if (!empty($company['headquarters_location'])): ?> &middot; <i class="bi bi-geo-alt me-1"></i><?= e($company['headquarters_location']) ?><?php endif; ?>
                    <?php if (!empty($company['company_size'])): ?> &middot; <?= e($company['company_size']) ?> employees<?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($company['description'])): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-quote me-2 text-primary"></i>About</div>
            <div class="card-body"><p class="mb-0"><?= nl2br(e($company['description'])) ?></p></div>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-briefcase me-2 text-primary"></i>Open Roles</div>
        <div class="card-body">
            <?php if (empty($jobs)): ?>
                <p class="text-muted small mb-0">No open roles right now.</p>
            <?php endif; ?>
            <?php foreach ($jobs as $job): ?>
                <a href="<?= url('/jobs/' . $job['id']) ?>" class="d-block profile-row text-decoration-none text-reset">
                    <strong><?= e($job['title']) ?></strong>
                    <div class="small text-muted">
                        <?= e(ucfirst(str_replace('_', ' ', $job['type']))) ?>
                        <?php if (!empty($job['location'])): ?> &middot; <?= e($job['location']) ?><?php endif; ?>
                        <?php if ($job['is_remote']): ?> &middot; Remote<?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <p class="text-center text-muted small"><a href="<?= url('/companies') ?>">&larr; Back to all companies</a></p>
</div>
