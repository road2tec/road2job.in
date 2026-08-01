<h1 class="h4 mb-1">Job Postings</h1>
<p class="text-muted mb-4">Create and manage your job and internship listings.</p>

<?php if ($company === null): ?>
    <div class="alert alert-warning">
        Please <a href="<?= url('/dashboard/company') ?>">set up your company profile</a> before posting a job.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-briefcase me-2 text-primary"></i>Your postings</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#jobModal" onclick="resetResourceModal('jobModal', '<?= url('/dashboard/jobs') ?>')" <?= $company === null ? 'disabled' : '' ?>>Post a job</button>
    </div>
    <div class="card-body">
        <?php if (empty($jobs)): ?>
            <p class="text-muted small mb-0">No job postings yet.</p>
        <?php endif; ?>
        <?php foreach ($jobs as $row): ?>
            <?php $statusBadge = ['draft' => 'text-bg-secondary', 'published' => 'text-bg-success', 'closed' => 'text-bg-danger'][$row['status']] ?? 'text-bg-secondary'; ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['title']) ?></strong>
                    <span class="badge <?= $statusBadge ?> ms-1"><?= e(ucfirst($row['status'])) ?></span>
                    <br>
                    <span class="small text-muted">
                        <?= e(ucfirst(str_replace('_', ' ', $row['type']))) ?>
                        <?php if (!empty($row['location'])): ?> &middot; <?= e($row['location']) ?><?php endif; ?>
                        <?php if ($row['is_remote']): ?> &middot; Remote<?php endif; ?>
                        &middot; <?= e(ucfirst($row['experience_level'])) ?>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#jobModal"
                        onclick='openResourceModal("jobModal", "<?= url('/dashboard/jobs/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/jobs/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this job posting?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php Core\View::partial('dashboard/employer/_job_modals'); ?>

<script src="<?= asset('js/profile.js') ?>"></script>
