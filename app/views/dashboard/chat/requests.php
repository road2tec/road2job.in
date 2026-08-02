<?php
$statusBadge = [
    'pending' => 'text-bg-warning',
    'accepted' => 'text-bg-success',
    'declined' => 'text-bg-secondary',
];
?>
<h1 class="h4 mb-1">Chat Requests</h1>
<p class="text-muted mb-4"><?= $isStudent ? 'Employers who want to start a conversation with you.' : 'Chat requests you\'ve sent to candidates.' ?></p>

<?php if (empty($requests)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-chat-dots"></i></div>
            <h2 class="fw-semibold h5">No chat requests yet</h2>
            <p class="text-muted mb-0"><?= $isStudent ? 'When an employer wants to chat, it\'ll show up here.' : 'Send a chat request from an applicant\'s profile.' ?></p>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($requests as $row): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                <?php if ($isStudent): ?>
                    <?= e($row['employer_name']) ?>
                    <?php if (!empty($row['company_name'])): ?><span class="text-muted">&middot; <?= e($row['company_name']) ?></span><?php endif; ?>
                <?php else: ?>
                    <?= e($row['student_name']) ?>
                <?php endif; ?>
                <span class="badge <?= $statusBadge[$row['status']] ?? 'text-bg-secondary' ?> ms-2"><?= e(ucfirst($row['status'])) ?></span>
            </span>
            <span class="small text-muted"><?= e($row['created_at']) ?></span>
        </div>
        <div class="card-body">
            <?php if (!empty($row['message'])): ?>
                <p class="small mb-3">"<?= nl2br(e($row['message'])) ?>"</p>
            <?php endif; ?>

            <?php if ($isStudent && $row['status'] === 'pending'): ?>
                <div class="d-flex gap-2">
                    <form method="post" action="<?= url('/dashboard/chat/requests/' . $row['id']) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check2 me-1"></i>Accept</button>
                    </form>
                    <form method="post" action="<?= url('/dashboard/chat/requests/' . $row['id']) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="declined">
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Decline</button>
                    </form>
                </div>
            <?php elseif ($row['status'] === 'accepted'): ?>
                <a href="<?= url('/dashboard/chat') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-chat-dots me-1"></i>Open conversation</a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
