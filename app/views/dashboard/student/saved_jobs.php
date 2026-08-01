<h1 class="h4 mb-1">Saved Jobs</h1>
<p class="text-muted mb-4">Jobs you've bookmarked to apply to later.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-bookmark-heart me-2 text-primary"></i>Saved</div>
    <div class="card-body">
        <?php if (empty($savedJobs)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-bookmark-heart"></i></div>
                <h2 class="fw-semibold h5">No saved jobs yet</h2>
                <p class="text-muted mb-3">Bookmark a role while browsing to come back to it later.</p>
                <a href="<?= url('/jobs') ?>" class="btn btn-outline-primary btn-sm">Browse jobs</a>
            </div>
        <?php endif; ?>
        <?php foreach ($savedJobs as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <a href="<?= url('/jobs/' . $row['job_posting_id']) ?>" class="text-reset"><strong><?= e($row['job_title']) ?></strong></a>
                    <?php if ($row['job_status'] !== 'published'): ?><span class="badge text-bg-secondary ms-1">No longer open</span><?php endif; ?>
                    <br>
                    <span class="small text-muted">
                        <?= e($row['company_name']) ?>
                        <?= e(ucfirst(str_replace('_', ' ', $row['job_type']))) ?>
                        <?php if (!empty($row['job_location'])): ?> &middot; <?= e($row['job_location']) ?><?php endif; ?>
                    </span>
                </div>
                <form method="post" action="<?= url('/jobs/' . $row['job_posting_id'] . '/save') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
