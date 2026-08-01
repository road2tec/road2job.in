<div class="text-center py-5">
    <p class="text-uppercase text-muted small mb-4" style="letter-spacing: 3px;">Road2Job</p>
    <h1 class="display-6 fw-bold mb-4">Certificate of Completion</h1>
    <p class="mb-2">This certifies that</p>
    <h2 class="h3 fw-bold mb-3"><?= e($user['full_name']) ?></h2>
    <p class="mb-4">has successfully completed</p>
    <h3 class="h4 fw-semibold text-primary mb-2"><?= e($course['title']) ?></h3>
    <p class="text-muted small mb-5">
        <?= e(ucfirst($course['format'])) ?>
        <?php if (!empty($institute)): ?> offered by <?= e($institute['name']) ?><?php endif; ?>
    </p>
    <p class="text-muted small mb-0" style="letter-spacing: 1px;">Issued by Road2Job &middot; Course-completion record, not an accredited qualification</p>
</div>
