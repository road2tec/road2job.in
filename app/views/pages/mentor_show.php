<?php
$serviceLabels = [
    'mentorship' => 'General Mentorship',
    'resume-review' => 'Resume Review',
    'portfolio-review' => 'Portfolio Review',
    'career-counseling' => 'Career Counseling',
    'mock-interview-feedback' => 'Mock Interview Feedback',
];
?>
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <?php if ($service !== 'mentorship'): ?>
            <div class="alert alert-info">Requesting: <strong><?= e($serviceLabels[$service] ?? $service) ?></strong></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center feature-icon" style="width:64px;height:64px;">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <div>
                        <h1 class="h4 fw-bold mb-1"><?= e($mentor['full_name']) ?></h1>
                        <div class="text-muted"><?= e($mentor['expertise']) ?></div>
                    </div>
                </div>

                <?php if (!empty($mentor['bio'])): ?>
                    <h2 class="h6 fw-semibold">About</h2>
                    <p class="mb-3"><?= nl2br(e($mentor['bio'])) ?></p>
                <?php endif; ?>

                <?php if (!empty($mentor['availability_note'])): ?>
                    <p class="small text-muted mb-0"><i class="bi bi-clock me-1"></i><?= e($mentor['availability_note']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if ($isStudent): ?>
                    <?php if ($alreadyRequested): ?>
                        <p class="mb-0"><span class="badge text-bg-warning p-2">Request pending</span></p>
                    <?php else: ?>
                        <h2 class="h6 fw-semibold mb-2">Request <?= e(strtolower($serviceLabels[$service] ?? $service)) ?></h2>
                        <form method="post" action="<?= url('/mentors/' . $mentor['id'] . '/request') ?>" data-guard-submit>
                            <?= csrf_field() ?>
                            <input type="hidden" name="service" value="<?= e($service) ?>">
                            <?php if ($mockSession !== ''): ?>
                                <input type="hidden" name="mock_interview_session_id" value="<?= e($mockSession) ?>">
                            <?php endif; ?>
                            <textarea name="message" class="form-control mb-2" rows="3" placeholder="What would you like guidance on?"></textarea>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0"><a href="<?= url('/login') ?>">Log in as a student</a> to request mentorship.</p>
                <?php endif; ?>
            </div>
        </div>

        <p class="text-center mt-4"><a href="<?= url('/mentors') ?>">&larr; Back to all mentors</a></p>
    </div>
</section>
