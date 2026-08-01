<?php
Core\View::partial('partials/page_header', [
    'title' => 'Blog',
    'subtitle' => 'Career advice, interview prep, and placement insights from the Road2Job team.',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-journal-text"></i></div>
                <h2 class="fw-semibold h5">No posts yet</h2>
                <p class="text-muted mb-0">Check back soon for career advice and placement insights.</p>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($posts as $post): ?>
                <a href="<?= url('/blog/' . $post['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-1"><?= e($post['title']) ?></h2>
                        <div class="small text-muted">by <?= e($post['author_name']) ?> &middot; <?= e(date('F j, Y', strtotime($post['published_at']))) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
