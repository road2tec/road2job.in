<?php
$categoryLabels = [
    'hackathon' => 'Hackathon',
    'job-fair' => 'Job Fair',
    'seminar' => 'Seminar',
    'webinar' => 'Webinar',
];
?>
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <div class="card mb-4">
            <div class="card-body">
                <span class="badge text-bg-light border mb-2"><?= e($categoryLabels[$event['category']] ?? $event['category']) ?></span>
                <?php if ((int) $event['is_online'] === 1): ?><span class="badge text-bg-light border mb-2">Online</span><?php endif; ?>
                <h1 class="h4 fw-bold mb-2"><?= e($event['title']) ?></h1>
                <p class="small text-muted mb-3">
                    Hosted by <?= e($event['organizer_name']) ?> &middot; <?= e(date('F j, Y g:i A', strtotime($event['starts_at']))) ?>
                    <?php if (!empty($event['ends_at'])): ?> &ndash; <?= e(date('F j, Y g:i A', strtotime($event['ends_at']))) ?><?php endif; ?>
                    <?php if (!empty($event['location'])): ?><br>Location: <?= e($event['location']) ?><?php endif; ?>
                </p>
                <?php if (!empty($event['description'])): ?>
                    <p class="mb-0"><?= nl2br(e($event['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$isLoggedIn): ?>
            <p class="text-muted small"><a href="<?= url('/login') ?>">Log in</a> to register for this event.</p>
        <?php elseif ($isRegistered): ?>
            <p class="text-success small mb-0"><i class="bi bi-check-circle-fill me-1"></i>You are registered for this event. See it under <a href="<?= url('/dashboard/events') ?>">My Events</a>.</p>
        <?php else: ?>
            <form method="post" action="<?= url('/events/' . $event['id'] . '/register') ?>" data-guard-submit>
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
        <?php endif; ?>

        <p class="text-center mt-4"><a href="<?= url('/events') ?>">&larr; Back to Events</a></p>
    </div>
</section>
