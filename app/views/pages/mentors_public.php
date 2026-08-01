<?php
$serviceLabels = [
    'mentorship' => 'General Mentorship',
    'resume-review' => 'Resume Review',
    'portfolio-review' => 'Portfolio Review',
    'career-counseling' => 'Career Counseling',
    'mock-interview-feedback' => 'Mock Interview Feedback',
];
$passThroughQuery = ($service !== 'mentorship' ? 'service=' . urlencode($service) : '')
    . ($mockSession !== '' ? (($service !== 'mentorship' ? '&' : '') . 'mock_session=' . urlencode($mockSession)) : '');

Core\View::partial('partials/page_header', [
    'title' => 'Mentors',
    'subtitle' => 'Get career guidance from mentors on Road2Job.',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if ($service !== 'mentorship'): ?>
            <div class="alert alert-info">Pick a mentor to request: <strong><?= e($serviceLabels[$service] ?? $service) ?></strong></div>
        <?php endif; ?>

        <?php if (empty($mentors)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-person-badge"></i></div>
                <h2 class="fw-semibold h5">No mentors listed yet</h2>
                <p class="text-muted mb-0">Check back soon - mentors are added as they join the platform.</p>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($mentors as $mentor): ?>
                <a href="<?= url('/mentors/' . $mentor['id']) . ($passThroughQuery !== '' ? '?' . $passThroughQuery : '') ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body d-flex gap-3 align-items-start">
                        <div class="listing-card__logo-fallback"><i class="bi bi-person-badge"></i></div>
                        <div>
                            <h2 class="h6 fw-semibold mb-1"><?= e($mentor['full_name']) ?></h2>
                            <div class="small text-muted"><?= e($mentor['expertise']) ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
