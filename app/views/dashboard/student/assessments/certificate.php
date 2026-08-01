<div class="text-center py-5">
    <p class="text-uppercase text-muted small mb-4" style="letter-spacing: 3px;">Road2Job</p>
    <h1 class="display-6 fw-bold mb-4">Certificate of Achievement</h1>
    <p class="mb-2">This certifies that</p>
    <h2 class="h3 fw-bold mb-3"><?= e($user['full_name']) ?></h2>
    <p class="mb-4">has successfully passed the</p>
    <h3 class="h4 fw-semibold text-primary mb-4"><?= e(ucfirst($attempt['category'])) ?> Assessment</h3>
    <p class="mb-1">with a score of <strong><?= (int) $attempt['percent'] ?>%</strong></p>
    <p class="text-muted small mb-5">Completed on <?= e(date('F j, Y', strtotime($attempt['completed_at']))) ?></p>
    <p class="text-muted small mb-0" style="letter-spacing: 1px;">Issued by Road2Job &middot; Practice Assessment, not a formal qualification</p>
</div>
