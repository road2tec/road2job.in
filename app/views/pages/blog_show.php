<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-2"><?= e($post['title']) ?></h1>
                <p class="small text-muted mb-3">by <?= e($post['author_name']) ?> &middot; <?= e(date('F j, Y', strtotime($post['published_at']))) ?></p>
                <p class="mb-0"><?= nl2br(e($post['body'])) ?></p>
            </div>
        </div>

        <p class="text-center mt-4"><a href="<?= url('/blog') ?>">&larr; Back to Blog</a></p>
    </div>
</section>
