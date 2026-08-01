<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-2"><?= e($roadmap['title']) ?></h1>
                <p class="text-muted small mb-2">by <?= e($roadmap['institute_name']) ?></p>
                <?php if (!empty($roadmap['description'])): ?><p class="mb-0"><?= nl2br(e($roadmap['description'])) ?></p><?php endif; ?>
            </div>
        </div>

        <?php if (empty($milestones)): ?>
            <p class="text-muted small">No milestones added yet.</p>
        <?php endif; ?>

        <?php foreach ($milestones as $i => $milestone): ?>
            <div class="card mb-3">
                <div class="card-body d-flex gap-3">
                    <div class="feature-icon flex-shrink-0" style="width:40px;height:40px;"><?= $i + 1 ?></div>
                    <div>
                        <h2 class="h6 fw-semibold mb-1"><?= e($milestone['title']) ?></h2>
                        <?php if (!empty($milestone['description'])): ?><p class="small text-muted mb-1"><?= e($milestone['description']) ?></p><?php endif; ?>
                        <?php if (!empty($milestone['course_title'])): ?>
                            <a href="<?= url('/institutes') ?>" class="small">Related course: <?= e($milestone['course_title']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <p class="text-center mt-4"><a href="<?= url('/roadmaps') ?>">&larr; Back to all roadmaps</a></p>
    </div>
</section>
