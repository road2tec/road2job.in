<?php
$categoryLabels = [
    'discussion' => 'Discussion',
    'question' => 'Question',
    'interview-experience' => 'Interview Experience',
    'success-story' => 'Success Story',
];

ob_start();
?>
<div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('/community') ?>" class="filter-chip <?= $activeCategory === null ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $category): ?>
            <a href="<?= url('/community?category=' . $category) ?>" class="filter-chip <?= $activeCategory === $category ? 'active' : '' ?>"><?= e($categoryLabels[$category]) ?></a>
        <?php endforeach; ?>
    </div>
    <a href="<?= url('/community/new') ?>" class="btn btn-primary btn-sm">New Post</a>
</div>
<?php
$below = ob_get_clean();

Core\View::partial('partials/page_header', [
    'title' => 'Community',
    'subtitle' => 'Discussions, questions, interview experiences and success stories.',
    'below' => $below,
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-chat-square-text"></i></div>
                <h2 class="fw-semibold h5">No posts yet</h2>
                <p class="text-muted mb-3">Be the first to start a discussion, ask a question, or share your experience.</p>
                <a href="<?= url('/community/new') ?>" class="btn btn-primary btn-sm">New Post</a>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($posts as $post): ?>
                <a href="<?= url('/community/' . $post['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body">
                        <span class="badge text-bg-light border mb-2"><?= e($categoryLabels[$post['category']] ?? $post['category']) ?></span>
                        <?php if (!empty($post['tag'])): ?><span class="badge text-bg-light border mb-2"><?= e($post['tag']) ?></span><?php endif; ?>
                        <h2 class="h6 fw-semibold mb-1"><?= e($post['title']) ?></h2>
                        <div class="small text-muted">
                            by <?= e($post['author_name']) ?> &middot; <?= e($post['created_at']) ?> &middot; <?= (int) $post['views'] ?> views
                            <?php if ($post['category'] === 'question' && !empty($post['accepted_reply_id'])): ?> &middot; <span class="text-success"><i class="bi bi-check-circle-fill"></i> Answered</span><?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
