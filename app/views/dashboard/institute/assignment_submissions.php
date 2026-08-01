<h1 class="h4 mb-1">Submissions</h1>
<p class="text-muted mb-4">for <strong><?= e($assignment['title']) ?></strong></p>

<p class="mb-3"><a href="<?= url('/dashboard/institute/courses/' . $assignment['course_id'] . '/assignments') ?>">&larr; Back to assignments</a></p>

<?php if (empty($submissions)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">No submissions yet.</p>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($submissions as $row): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><?= e($row['student_name']) ?></span>
            <span class="badge <?= $row['status'] === 'reviewed' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst($row['status'])) ?></span>
        </div>
        <div class="card-body">
            <?php if (!empty($row['submission_text'])): ?>
                <p class="small mb-2"><?= nl2br(e($row['submission_text'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($row['submission_file_path'])): ?>
                <p class="small mb-3"><a href="<?= url($row['submission_file_path']) ?>" target="_blank">View attached file</a></p>
            <?php endif; ?>
            <p class="small text-muted mb-3">Submitted <?= e($row['submitted_at']) ?></p>

            <form method="post" action="<?= url('/dashboard/institute/submissions/' . $row['id'] . '/review') ?>" data-guard-submit>
                <?= csrf_field() ?>
                <div class="mb-2">
                    <label class="form-label small" for="assignment-submissions-feedback">Feedback</label>
                    <textarea id="assignment-submissions-feedback" name="feedback" class="form-control" rows="2"><?= e($row['feedback'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><?= $row['status'] === 'reviewed' ? 'Update feedback' : 'Mark reviewed' ?></button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
