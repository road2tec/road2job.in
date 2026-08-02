<?php
$statusText = null;
if (!empty($otherParty['last_login_at'])) {
    $minutesAgo = (time() - strtotime($otherParty['last_login_at'])) / 60;
    if ($minutesAgo <= 15) {
        $statusText = 'Active recently';
    } elseif ($minutesAgo <= 1440) {
        $statusText = 'Active today';
    }
}
?>
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= url('/dashboard/chat') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h1 class="h5 mb-0"><?= e($otherParty['full_name'] ?? 'Conversation') ?></h1>
        <?php if ($statusText !== null): ?>
            <span class="small text-muted"><?= e($statusText) ?></span>
        <?php endif; ?>
    </div>
</div>

<?php if ($thread['status'] === 'suspended'): ?>
    <div class="alert alert-warning small">
        <i class="bi bi-exclamation-triangle me-1"></i>This conversation has been suspended by an administrator. You cannot send new messages.
    </div>
<?php endif; ?>

<div class="card" id="chat-thread-root"
     data-csrf="<?= csrf_token() ?>"
     data-current-user="<?= (int) $user['id'] ?>"
     data-send-url="<?= url('/dashboard/chat/' . $thread['id'] . '/messages') ?>"
     data-poll-url="<?= url('/dashboard/chat/' . $thread['id'] . '/poll') ?>"
     data-read-url="<?= url('/dashboard/chat/' . $thread['id'] . '/read') ?>">
    <div class="card-body">
        <div id="chat-messages" class="d-flex flex-column gap-3 mb-3" style="max-height: 480px; overflow-y: auto;">
            <?php if (empty($messages)): ?>
                <p class="text-muted small text-center mb-0">No messages yet - say hello.</p>
            <?php endif; ?>
            <?php foreach ($messages as $message): ?>
                <?php Core\View::partial('dashboard/chat/_message_bubble', ['message' => $message, 'currentUserId' => (int) $user['id']]); ?>
            <?php endforeach; ?>
        </div>

        <?php if ($thread['status'] !== 'suspended'): ?>
            <form id="chat-send-form" class="border-top pt-3">
                <div class="d-flex gap-2 align-items-end">
                    <div class="flex-fill">
                        <textarea id="chat-message-body" class="form-control" rows="1" maxlength="2000" placeholder="Type a message..."></textarea>
                    </div>
                    <label class="btn btn-sm btn-outline-secondary mb-0" title="Attach a file (resume, image)">
                        <i class="bi bi-paperclip"></i>
                        <input type="file" id="chat-attachment-input" class="d-none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i></button>
                </div>
                <div id="chat-attachment-name" class="small text-muted mt-1"></div>
                <div id="chat-send-error" class="small text-danger mt-1"></div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="<?= asset('js/chat_thread.js') ?>"></script>
