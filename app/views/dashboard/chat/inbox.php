<h1 class="h4 mb-1">Messages</h1>
<p class="text-muted mb-4">This page checks for new messages automatically while you have a conversation open - no need to refresh.</p>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= url('/dashboard/chat/requests') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope me-1"></i>Chat requests</a>
</div>

<?php if (empty($threads)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-chat-dots"></i></div>
            <h2 class="fw-semibold h5">No conversations yet</h2>
            <p class="text-muted mb-0">Accepted chat requests will show up here.</p>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <?php foreach ($threads as $i => $thread): ?>
        <a href="<?= url('/dashboard/chat/' . $thread['id']) ?>" class="text-decoration-none text-reset">
            <div class="card-body d-flex justify-content-between align-items-center gap-3 <?= $i > 0 ? 'border-top' : '' ?>">
                <div class="flex-fill">
                    <div class="d-flex align-items-center gap-2">
                        <strong><?= e($thread['other_party_name']) ?></strong>
                        <?php if (!empty($thread['other_party_company'])): ?>
                            <span class="small text-muted">&middot; <?= e($thread['other_party_company']) ?></span>
                        <?php endif; ?>
                        <?php if ($thread['status'] === 'suspended'): ?>
                            <span class="badge text-bg-secondary">Suspended</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($thread['last_message_body'])): ?>
                        <p class="small text-muted mb-0 text-truncate" style="max-width: 480px;"><?= e($thread['last_message_body']) ?></p>
                    <?php else: ?>
                        <p class="small text-muted mb-0 fst-italic">No messages yet</p>
                    <?php endif; ?>
                </div>
                <div class="text-end flex-shrink-0">
                    <?php if (!empty($thread['last_message_at'])): ?>
                        <div class="small text-muted"><?= e($thread['last_message_at']) ?></div>
                    <?php endif; ?>
                    <?php if ((int) $thread['unread_count'] > 0): ?>
                        <span class="badge text-bg-primary rounded-pill"><?= (int) $thread['unread_count'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>
