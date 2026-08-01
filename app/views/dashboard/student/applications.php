<h1 class="h4 mb-1">Applications</h1>
<p class="text-muted mb-4">Track every job you've applied to.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-send-check me-2 text-primary"></i>Your applications</div>
    <div class="card-body">
        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-send-check"></i></div>
                <h2 class="fw-semibold h5">No applications yet</h2>
                <p class="text-muted mb-3">When you apply to a role, you'll be able to track its status here.</p>
                <a href="<?= url('/jobs') ?>" class="btn btn-outline-primary btn-sm">Browse jobs</a>
            </div>
        <?php endif; ?>
        <?php foreach ($applications as $row): ?>
            <?php $statusBadge = [
                'applied' => 'text-bg-secondary',
                'under_review' => 'text-bg-info',
                'shortlisted' => 'text-bg-warning',
                'rejected' => 'text-bg-danger',
                'selected' => 'text-bg-success',
            ][$row['status']] ?? 'text-bg-secondary'; ?>
            <div class="profile-row">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <a href="<?= url('/jobs/' . $row['job_posting_id']) ?>" class="text-reset"><strong><?= e($row['job_title']) ?></strong></a>
                        <span class="badge <?= $statusBadge ?> ms-1"><?= e(ucfirst(str_replace('_', ' ', $row['status']))) ?></span>
                        <?php if ($row['matchScore']['percent'] !== null): ?>
                            <span class="badge text-bg-light border ms-1"><?= (int) $row['matchScore']['percent'] ?>% match</span>
                        <?php endif; ?>
                        <br>
                        <span class="small text-muted"><?= e($row['company_name']) ?> &middot; Applied <?= e($row['created_at']) ?></span>
                        <?php if (!empty($row['cover_note'])): ?><p class="small text-muted mb-0 mt-1">"<?= e($row['cover_note']) ?>"</p><?php endif; ?>
                    </div>
                    <?php if (!empty($row['timeline'])): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#timeline-<?= (int) $row['id'] ?>">
                            <i class="bi bi-clock-history me-1"></i>Timeline
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (!empty($row['timeline'])): ?>
                    <div class="collapse mt-3" id="timeline-<?= (int) $row['id'] ?>">
                        <ul class="timeline">
                            <?php foreach ($row['timeline'] as $event): ?>
                                <li class="timeline-item">
                                    <div class="fw-semibold small"><i class="bi <?= e($event['icon']) ?> me-1 text-primary"></i><?= e($event['label']) ?></div>
                                    <div class="small text-muted"><?= e(date('d M Y, h:i A', strtotime($event['at']))) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
