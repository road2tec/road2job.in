<?php
$typeLabels = ['announcement' => 'Announcement', 'placement_news' => 'Placement News', 'achievement' => 'Achievement', 'event' => 'Event', 'course_update' => 'Course Update', 'general' => 'General'];
$typeBadge = [
    'announcement' => 'text-bg-primary',
    'placement_news' => 'text-bg-success',
    'achievement' => 'text-bg-warning',
    'event' => 'text-bg-info',
    'course_update' => 'text-bg-secondary',
    'general' => 'text-bg-light border',
];
?>
<h1 class="h4 mb-1">Institute Updates</h1>
<p class="text-muted mb-4">Announcements, placements and events from training institutes on Road2Job.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-megaphone me-2 text-primary"></i>Latest updates</div>
    <div class="card-body">
        <?php if (empty($updates)): ?>
            <p class="text-muted small mb-0">No institute updates yet. Browse <a href="<?= url('/institutes') ?>">institutes</a> to see their profiles.</p>
        <?php endif; ?>
        <?php foreach ($updates as $update): ?>
            <div class="profile-row d-flex gap-3 align-items-start">
                <?php if (!empty($update['institute_logo_path'])): ?>
                    <img src="<?= url($update['institute_logo_path']) ?>" alt="" class="rounded-circle flex-shrink-0" style="width:40px;height:40px;object-fit:cover;">
                <?php else: ?>
                    <div class="feature-icon flex-shrink-0"><i class="bi bi-mortarboard"></i></div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                        <div>
                            <a href="<?= url('/institutes/' . $update['institute_id']) ?>" class="fw-semibold text-reset text-decoration-none"><?= e($update['institute_name']) ?></a>
                            <?php if (!empty($update['institute_city'])): ?><span class="small text-muted"> &middot; <?= e($update['institute_city']) ?></span><?php endif; ?>
                        </div>
                        <span class="small text-muted"><?= e(display_date($update['created_at'] ?? '', '')) ?></span>
                    </div>
                    <span class="badge <?= $typeBadge[$update['category']] ?? 'text-bg-light border' ?> my-1"><?= e($typeLabels[$update['category']] ?? $update['category']) ?></span>
                    <div class="fw-semibold"><?= e($update['title']) ?></div>
                    <p class="small text-muted mb-0"><?= nl2br(e($update['body'])) ?></p>
                </div>
            </div>
            <hr class="text-muted opacity-25">
        <?php endforeach; ?>
    </div>
</div>
