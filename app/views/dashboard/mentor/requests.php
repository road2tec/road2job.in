<?php
$serviceLabels = [
    'mentorship' => 'General Mentorship',
    'resume-review' => 'Resume Review',
    'portfolio-review' => 'Portfolio Review',
    'career-counseling' => 'Career Counseling',
    'mock-interview-feedback' => 'Mock Interview Feedback',
];
?>
<h1 class="h4 mb-1">Mentorship Requests</h1>
<p class="text-muted mb-4">Students who've asked for your guidance.</p>

<?php if (empty($requests)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">No requests yet.</p>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($requests as $row): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <?= e($row['student_name']) ?>
                <span class="badge text-bg-light border ms-2"><?= e($serviceLabels[$row['service_type']] ?? $row['service_type']) ?></span>
            </span>
            <span class="small text-muted"><?= e($row['created_at']) ?></span>
        </div>
        <div class="card-body">
            <p class="small text-muted mb-1"><?= e($row['student_email']) ?></p>
            <?php if (!empty($row['message'])): ?>
                <p class="small mb-2"><?= nl2br(e($row['message'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($row['resume_snapshot'])): ?>
                <p class="small mb-2"><a href="<?= url('/dashboard/mentor/requests/' . $row['id'] . '/resume') ?>" target="_blank">View submitted resume</a></p>
            <?php endif; ?>
            <?php if (!empty($row['mock_interview_session_id'])): ?>
                <p class="small mb-2"><span class="text-muted">Mock interview session #<?= (int) $row['mock_interview_session_id'] ?> (ask the student to share recordings, or review notes together)</span></p>
            <?php endif; ?>

            <form method="post" action="<?= url('/dashboard/mentor/requests/' . $row['id']) ?>">
                <?= csrf_field() ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small" for="requests-status">Status</label>
                        <select id="requests-status" name="status" class="form-select form-select-sm">
                            <?php foreach (['pending', 'accepted', 'declined'] as $statusOption): ?>
                                <option value="<?= $statusOption ?>" <?= $row['status'] === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small" for="requests-feedback">Feedback</label>
                        <textarea id="requests-feedback" name="feedback" class="form-control form-control-sm" rows="1"><?= e($row['feedback'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
