<section class="py-5">
    <div class="container text-center" style="max-width: 700px;">
        <div class="feature-icon mx-auto mb-3"><i class="bi <?= e($page['icon']) ?>"></i></div>
        <span class="badge text-bg-light border mb-2">Not Yet Available</span>
        <h1 class="fw-bold mb-3"><?= e($page['title']) ?></h1>
        <p class="lead text-muted"><?= e($page['description']) ?></p>
        <p class="text-muted small"><?= e(ucfirst($page['phase'])) ?>.</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="<?= url('/') ?>" class="btn btn-outline-primary">Back to Home</a>
            <a href="<?= url('/register') ?>" class="btn btn-primary">Create your account</a>
        </div>
    </div>
</section>
