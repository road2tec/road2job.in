<?php
$categoryLabels = [
    'discussion' => 'Discussion',
    'question' => 'Question',
    'interview-experience' => 'Interview Experience',
    'success-story' => 'Success Story',
];
?>
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <div class="card mb-4">
            <div class="card-body">
                <span class="badge text-bg-light border mb-2"><?= e($categoryLabels[$post['category']] ?? $post['category']) ?></span>
                <?php if (!empty($post['tag'])): ?><span class="badge text-bg-light border mb-2"><?= e($post['tag']) ?></span><?php endif; ?>
                <h1 class="h4 fw-bold mb-2"><?= e($post['title']) ?></h1>
                <p class="small text-muted mb-3">by <?= e($post['author_name']) ?> &middot; <?= e($post['created_at']) ?> &middot; <?= (int) $post['views'] ?> views</p>
                <p class="mb-0"><?= nl2br(e($post['body'])) ?></p>
            </div>
        </div>

        <h2 class="h6 fw-semibold mb-3"><?= count($replies) ?> repl<?= count($replies) === 1 ? 'y' : 'ies' ?></h2>

        <?php foreach ($replies as $reply): ?>
            <div class="card mb-3 <?= (int) $reply['id'] === (int) ($post['accepted_reply_id'] ?? 0) ? 'border-success' : '' ?>">
                <div class="card-body">
                    <?php if ((int) $reply['id'] === (int) ($post['accepted_reply_id'] ?? 0)): ?>
                        <span class="badge text-bg-success mb-2"><i class="bi bi-check-circle-fill me-1"></i>Accepted Answer</span>
                    <?php endif; ?>
                    <p class="mb-2"><?= nl2br(e($reply['body'])) ?></p>
                    <p class="small text-muted mb-0">
                        by <?= e($reply['author_name']) ?> &middot; <?= e($reply['created_at']) ?>
                        <?php if ($isAuthor && $post['category'] === 'question' && empty($post['accepted_reply_id'])): ?>
                            <form method="post" action="<?= url('/community/' . $post['id'] . '/replies/' . $reply['id'] . '/accept') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-success ms-2">Mark as accepted</button>
                            </form>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($isLoggedIn): ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-2">Post a reply</h2>
                    <form method="post" action="<?= url('/community/' . $post['id'] . '/replies') ?>" data-guard-submit>
                        <?= csrf_field() ?>
                        <textarea name="body" class="form-control mb-2" rows="3" required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm">Reply</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted small"><a href="<?= url('/login') ?>">Log in</a> to reply.</p>
        <?php endif; ?>

        <p class="text-center mt-4"><a href="<?= url('/community') ?>">&larr; Back to Community</a></p>
    </div>
</section>
