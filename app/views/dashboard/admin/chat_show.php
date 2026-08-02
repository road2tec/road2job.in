<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= url('/admin/chats') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h5 mb-0">Conversation #<?= (int) $thread['id'] ?></h1>
</div>

<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="small text-muted">Read-only transcript for review - no messages can be sent from here.</span>
        <form method="post" action="<?= url('/admin/chats/' . $thread['id'] . '/status') ?>" class="d-flex gap-2 align-items-center">
            <?= csrf_field() ?>
            <?php if ($thread['status'] === 'active'): ?>
                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reason (optional)" style="width: 220px;">
                <input type="hidden" name="status" value="suspended">
                <button type="submit" class="btn btn-sm btn-outline-danger">Suspend</button>
            <?php else: ?>
                <input type="hidden" name="status" value="active">
                <button type="submit" class="btn btn-sm btn-outline-success">Reactivate</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!empty($thread['suspended_reason'])): ?>
    <div class="alert alert-secondary small">Suspended: <?= e($thread['suspended_reason']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body d-flex flex-column gap-3">
        <?php if (empty($messages)): ?>
            <p class="text-muted small text-center mb-0">No messages in this conversation.</p>
        <?php endif; ?>
        <?php foreach ($messages as $message): ?>
            <?php Core\View::partial('dashboard/chat/_message_bubble', ['message' => $message, 'currentUserId' => (int) $thread['employer_user_id']]); ?>
        <?php endforeach; ?>
    </div>
</div>
