<h1 class="h4 mb-1">Campus Drives</h1>
<p class="text-muted mb-4">Create and manage your campus recruitment drives.</p>

<?php if ($college === null): ?>
    <div class="alert alert-warning">
        Please <a href="<?= url('/dashboard/college') ?>">set up your college profile</a> before adding a drive.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-briefcase me-2 text-primary"></i>Your drives</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#driveModal" onclick="resetResourceModal('driveModal', '<?= url('/dashboard/college/drives') ?>')" <?= $college === null ? 'disabled' : '' ?>>Add drive</button>
    </div>
    <div class="card-body">
        <?php if (empty($drives)): ?>
            <p class="text-muted small mb-0">No campus drives added yet.</p>
        <?php endif; ?>
        <?php foreach ($drives as $row): ?>
            <?php $statusBadge = ['draft' => 'text-bg-secondary', 'published' => 'text-bg-success', 'closed' => 'text-bg-danger'][$row['status']] ?? 'text-bg-secondary'; ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['company_name']) ?></strong>
                    <span class="badge <?= $statusBadge ?> ms-1"><?= e(ucfirst($row['status'])) ?></span>
                    <br>
                    <span class="small text-muted">
                        <?php if (!empty($row['drive_date'])): ?><?= e($row['drive_date']) ?><?php endif; ?>
                        <?php if (!empty($row['eligible_departments'])): ?> &middot; <?= e($row['eligible_departments']) ?><?php endif; ?>
                        <?php if (!empty($row['min_cgpa'])): ?> &middot; Min CGPA <?= e($row['min_cgpa']) ?><?php endif; ?>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#driveModal"
                        onclick='openResourceModal("driveModal", "<?= url('/dashboard/college/drives/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/college/drives/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this drive?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Drive modal -->
<div class="modal fade" id="driveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Campus Drive</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="drives-company-name">Company name</label><input id="drives-company-name" type="text" name="company_name" class="form-control" required></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label" for="drives-drive-date">Drive date</label>
                            <input id="drives-drive-date" type="date" name="drive_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="drives-min-cgpa">Min CGPA</label>
                            <input id="drives-min-cgpa" type="number" name="min_cgpa" class="form-control" min="0" max="10" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="drives-status">Status</label>
                            <select id="drives-status" name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2"><label class="form-label" for="drives-eligible-departments">Eligible departments</label><input id="drives-eligible-departments" type="text" name="eligible_departments" class="form-control" placeholder="e.g. CS, IT, ECE"></div>
                    <div class="mb-2"><label class="form-label" for="drives-description">Description</label><textarea id="drives-description" name="description" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/profile.js') ?>"></script>
