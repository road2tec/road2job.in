<?php
$statusMeta = [
    'applied' => ['label' => 'Applied', 'badge' => 'text-bg-secondary', 'icon' => 'bi-send-check'],
    'under_review' => ['label' => 'Under Review', 'badge' => 'text-bg-info', 'icon' => 'bi-hourglass-split'],
    'shortlisted' => ['label' => 'Shortlisted', 'badge' => 'text-bg-warning', 'icon' => 'bi-star-fill'],
    'rejected' => ['label' => 'Not Selected', 'badge' => 'text-bg-danger', 'icon' => 'bi-x-circle'],
    'selected' => ['label' => 'Selected', 'badge' => 'text-bg-success', 'icon' => 'bi-trophy-fill'],
];
$statusCounts = array_count_values(array_column($applications, 'status'));
?>
<h1 class="h4 mb-1">Applications</h1>
<p class="text-muted mb-4">Track every job you've applied to, in one place.</p>

<?php if (!empty($applications)): ?>
    <div class="row g-3 mb-4">
        <?php foreach (['applied', 'under_review', 'shortlisted', 'selected'] as $key): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="feature-icon flex-shrink-0"><i class="bi <?= $statusMeta[$key]['icon'] ?>"></i></div>
                            <div>
                                <div class="h5 mb-0 fw-bold"><?= (int) ($statusCounts[$key] ?? 0) ?></div>
                                <div class="small text-muted"><?= e($statusMeta[$key]['label']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (empty($applications)): ?>
    <div class="empty-state">
        <div class="empty-state__icon"><i class="bi bi-send-check"></i></div>
        <h2 class="fw-semibold h5">No applications yet</h2>
        <p class="text-muted mb-3">When you apply to a role, you'll be able to track its status here.</p>
        <a href="<?= url('/jobs') ?>" class="btn btn-primary btn-sm">Browse jobs</a>
    </div>
<?php endif; ?>

<div class="d-flex flex-column gap-3">
    <?php foreach ($applications as $row): ?>
        <?php $meta = $statusMeta[$row['status']] ?? ['label' => ucfirst(str_replace('_', ' ', $row['status'])), 'badge' => 'text-bg-secondary', 'icon' => 'bi-circle']; ?>
        <div class="card listing-card">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-start">
                    <?php Core\View::partial('partials/company_logo', ['logoPath' => $row['company_logo_path'] ?? null, 'name' => $row['company_name']]); ?>
                    <div class="flex-fill">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <a href="<?= url('/jobs/' . $row['job_posting_id']) ?>" class="text-reset text-decoration-none"><h2 class="h6 fw-semibold mb-0"><?= e($row['job_title']) ?></h2></a>
                            <span class="badge <?= $meta['badge'] ?>"><i class="bi <?= $meta['icon'] ?> me-1"></i><?= e($meta['label']) ?></span>
                            <?php if ($row['matchScore']['percent'] !== null): ?>
                                <span class="badge text-bg-light border"><?= (int) $row['matchScore']['percent'] ?>% match</span>
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted mb-2">
                            <?= e($row['company_name']) ?>
                            <?php if (!empty($row['job_type'])): ?><span class="mx-1">&middot;</span><i class="bi bi-briefcase me-1"></i><?= e(ucfirst(str_replace('_', ' ', $row['job_type']))) ?><?php endif; ?>
                            <?php if (!empty($row['job_location'])): ?><span class="mx-1">&middot;</span><i class="bi bi-geo-alt me-1"></i><?= e($row['job_location']) ?><?php endif; ?>
                            <span class="mx-1">&middot;</span>Applied <?= e(display_date($row['created_at'] ?? '', '')) ?>
                        </div>
                        <?php if (!empty($row['cover_note'])): ?>
                            <p class="small text-muted mb-2 fst-italic">"<?= e($row['cover_note']) ?>"</p>
                        <?php endif; ?>
                        <?php if (!empty($row['timeline'])): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#timeline-<?= (int) $row['id'] ?>">
                                <i class="bi bi-clock-history me-1"></i>View timeline
                            </button>
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
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
