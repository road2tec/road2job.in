<?php
$isMine = (int) $message['sender_id'] === $currentUserId;
?>
<div class="d-flex <?= $isMine ? 'justify-content-end' : 'justify-content-start' ?>" data-message-id="<?= (int) $message['id'] ?>">
    <div class="p-2 px-3 rounded-3 <?= $isMine ? 'bg-primary text-white' : 'bg-body-tertiary' ?>" style="max-width: 75%;">
        <?php if (!empty($message['body'])): ?>
            <div class="small"><?= nl2br(e($message['body'])) ?></div>
        <?php endif; ?>

        <?php if (!empty($message['attachment_path'])): ?>
            <?php if ($message['attachment_type'] === 'image'): ?>
                <a href="<?= url($message['attachment_path']) ?>" target="_blank">
                    <img src="<?= url($message['attachment_path']) ?>" alt="<?= e($message['attachment_original_name'] ?? 'Attachment') ?>" class="rounded mt-1" style="max-width: 240px; max-height: 240px; object-fit: cover;">
                </a>
            <?php else: ?>
                <a href="<?= url($message['attachment_path']) ?>" target="_blank" class="d-flex align-items-center gap-2 mt-1 p-2 rounded <?= $isMine ? 'bg-white bg-opacity-25' : 'bg-white border' ?> text-decoration-none <?= $isMine ? 'text-white' : 'text-reset' ?>">
                    <i class="bi bi-file-earmark-<?= str_contains((string) ($message['attachment_mime'] ?? ''), 'pdf') ? 'pdf' : 'word' ?>"></i>
                    <span class="small"><?= e($message['attachment_original_name'] ?? 'Download file') ?></span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <div class="small mt-1 <?= $isMine ? 'text-white-50' : 'text-muted' ?>">
            <?= e(date('h:i A', strtotime($message['created_at']))) ?>
            <?php if ($isMine && !empty($message['read_at'])): ?>
                <i class="bi bi-check2-all ms-1" title="Read"></i>
            <?php endif; ?>
        </div>
    </div>
</div>
