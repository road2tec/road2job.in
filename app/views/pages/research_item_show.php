<?php
$typeLabels = [
    'research-paper' => 'Research Paper',
    'project' => 'Research Project',
    'publication' => 'Publication',
    'conference-paper' => 'Conference Paper',
    'patent' => 'Patent',
];
?>
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <div class="card">
            <div class="card-body">
                <span class="badge text-bg-light border mb-2"><?= e($typeLabels[$item['type']] ?? $item['type']) ?></span>
                <h1 class="h4 fw-bold mb-2"><?= e($item['title']) ?></h1>
                <p class="small text-muted mb-3">
                    by <?php if (!empty($item['author_username'])): ?><a href="<?= url('/u/' . $item['author_username']) ?>"><?= e($item['author_name']) ?></a><?php else: ?><?= e($item['author_name']) ?><?php endif; ?>
                    <?php if (!empty($item['publication_date'])): ?> &middot; <?= e($item['publication_date']) ?><?php endif; ?>
                </p>

                <?php if (!empty($item['authors_collaborators'])): ?>
                    <p class="small text-muted mb-3"><strong>Authors/Collaborators:</strong> <?= e($item['authors_collaborators']) ?></p>
                <?php endif; ?>

                <?php if (!empty($item['description'])): ?>
                    <p class="mb-3"><?= nl2br(e($item['description'])) ?></p>
                <?php endif; ?>

                <?php if (!empty($item['external_reference'])): ?>
                    <p class="small mb-2"><strong>Reference:</strong> <?= e($item['external_reference']) ?></p>
                <?php endif; ?>

                <?php if (!empty($item['attachment_path'])): ?>
                    <a href="<?= url($item['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Attachment</a>
                <?php endif; ?>
            </div>
        </div>

        <p class="text-center mt-4"><a href="<?= url('/research-hub') ?>">&larr; Back to Research Hub</a></p>
    </div>
</section>
