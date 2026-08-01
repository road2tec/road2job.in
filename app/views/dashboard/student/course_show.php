<h1 class="h4 mb-1"><?= e($course['title']) ?></h1>
<p class="text-muted mb-4">
    <?= e(ucfirst($course['format'])) ?>
    <?php if ($enrollment['status'] === 'completed'): ?><span class="badge text-bg-success ms-1">Completed</span><?php endif; ?>
</p>

<p class="mb-3"><a href="<?= url('/dashboard/my-learning') ?>">&larr; Back to My Learning</a></p>

<?php if ($enrollment['status'] === 'completed'): ?>
    <div class="alert alert-success">
        You've completed this course. <a href="<?= url('/dashboard/my-learning/' . $course['id'] . '/certificate') ?>" target="_blank">View your certificate</a>.
    </div>
<?php endif; ?>

<?php if (empty($assignments)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">No assignments posted yet.</p>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($assignments as $assignment): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><span class="badge text-bg-light border me-2"><?= e(ucfirst($assignment['type'])) ?></span><?= e($assignment['title']) ?></span>
            <?php if (!empty($assignment['mySubmission'])): ?>
                <span class="badge <?= $assignment['mySubmission']['status'] === 'reviewed' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst($assignment['mySubmission']['status'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($assignment['description'])): ?><p class="small mb-2"><?= nl2br(e($assignment['description'])) ?></p><?php endif; ?>
            <?php if (!empty($assignment['due_date'])): ?><p class="small text-muted mb-3">Due <?= e($assignment['due_date']) ?></p><?php endif; ?>

            <?php if (!empty($assignment['mySubmission']) && $assignment['mySubmission']['status'] === 'reviewed'): ?>
                <p class="small mb-2"><strong>Feedback:</strong> <?= nl2br(e($assignment['mySubmission']['feedback'])) ?></p>
            <?php endif; ?>

            <form method="post" action="<?= url('/dashboard/my-learning/' . $course['id'] . '/assignments/' . $assignment['id'] . '/submit') ?>" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="mb-2">
                    <label class="form-label small" for="course-show-submission-text"><?= !empty($assignment['mySubmission']) ? 'Update your submission' : 'Your submission' ?></label>
                    <textarea id="course-show-submission-text" name="submission_text" class="form-control" rows="3"><?= e($assignment['mySubmission']['submission_text'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label small" for="course-show-submission-file">Attach a file (optional)</label>
                    <input id="course-show-submission-file" type="file" name="submission_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><?= !empty($assignment['mySubmission']) ? 'Resubmit' : 'Submit' ?></button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
