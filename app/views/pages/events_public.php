<?php
$categoryLabels = [
    'hackathon' => 'Hackathon',
    'job-fair' => 'Job Fair',
    'seminar' => 'Seminar',
    'webinar' => 'Webinar',
];

ob_start();
?>
<div class="mt-3 d-flex gap-2 flex-wrap">
    <a href="<?= url('/events') ?>" class="filter-chip <?= $activeCategory === null ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $category): ?>
        <a href="<?= url('/events?category=' . $category) ?>" class="filter-chip <?= $activeCategory === $category ? 'active' : '' ?>"><?= e($categoryLabels[$category]) ?></a>
    <?php endforeach; ?>
</div>
<?php
$below = ob_get_clean();

Core\View::partial('partials/page_header', [
    'title' => 'Events',
    'subtitle' => 'Hackathons, job fairs, seminars and webinars hosted by employers, institutes, colleges and mentors.',
    'below' => $below,
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($events)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-calendar-event"></i></div>
                <h2 class="fw-semibold h5">No events scheduled yet</h2>
                <p class="text-muted mb-0">Check back soon, or browse another category.</p>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($events as $event): ?>
                <a href="<?= url('/events/' . $event['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body">
                        <span class="badge text-bg-light border mb-2"><?= e($categoryLabels[$event['category']] ?? $event['category']) ?></span>
                        <?php if ((int) $event['is_online'] === 1): ?><span class="badge text-bg-light border mb-2">Online</span><?php endif; ?>
                        <h2 class="h6 fw-semibold mb-1"><?= e($event['title']) ?></h2>
                        <div class="small text-muted">
                            by <?= e($event['organizer_name']) ?> &middot; <?= e(date('M j, Y g:i A', strtotime($event['starts_at']))) ?>
                            <?php if (!empty($event['location'])): ?> &middot; <?= e($event['location']) ?><?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
