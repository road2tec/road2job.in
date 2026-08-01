<h1 class="h4 mb-1">College Profile</h1>
<p class="text-muted mb-4">This information appears on your public college page.</p>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-bank me-2 text-primary"></i>College details</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/college') ?>" enctype="multipart/form-data" data-guard-submit>
            <?= csrf_field() ?>

            <div class="row g-3 mb-3 align-items-center">
                <div class="col-auto">
                    <?php if (!empty($college['logo_path'])): ?>
                        <img src="<?= url($college['logo_path']) ?>" alt="Logo" class="rounded border" loading="lazy" style="width:64px;height:64px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded d-flex align-items-center justify-content-center feature-icon" style="width:64px;height:64px;">
                            <i class="bi bi-bank"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <label class="form-label" for="profile-logo">College logo</label>
                    <input id="profile-logo" type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="profile-name">College name</label>
                    <input id="profile-name" type="text" name="name" class="form-control" value="<?= e($college['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-established-year">Established year</label>
                    <input id="profile-established-year" type="number" name="established_year" class="form-control" min="1800" max="<?= date('Y') ?>" value="<?= e($college['established_year'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-website">Website</label>
                    <input id="profile-website" type="url" name="website" class="form-control" placeholder="https://" value="<?= e($college['website'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-location">Location</label>
                    <input id="profile-location" type="text" name="location" class="form-control" placeholder="City, Country" value="<?= e($college['location'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="profile-description">About the college</label>
                    <textarea id="profile-description" name="description" class="form-control" rows="4"><?= e($college['description'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save changes</button>
        </form>
    </div>
</div>

<!-- Department-wise placement stats -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bar-chart me-2 text-primary"></i>Department-wise Placement Stats</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#deptStatModal" onclick="resetResourceModal('deptStatModal', '<?= url('/dashboard/college/department-stats') ?>')">Add</button>
    </div>
    <div class="card-body">
        <p class="text-muted small">These stats are college-reported and shown publicly labeled as such.</p>
        <?php if (empty($departmentStats)): ?>
            <p class="text-muted small mb-0">No stats added yet.</p>
        <?php endif; ?>
        <?php foreach ($departmentStats as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start">
                <div>
                    <strong><?= e($row['department_name']) ?></strong><?= !empty($row['academic_year']) ? ' - ' . e($row['academic_year']) : '' ?><br>
                    <span class="small text-muted">
                        <?php if (!empty($row['students_placed']) || !empty($row['total_students'])): ?><?= e($row['students_placed'] ?? 0) ?>/<?= e($row['total_students'] ?? '?') ?> placed &middot; <?php endif; ?>
                        <?php if (!empty($row['average_package'])): ?>Avg &#8377;<?= number_format((float) $row['average_package']) ?>/yr &middot; <?php endif; ?>
                        <?php if (!empty($row['highest_package'])): ?>Highest &#8377;<?= number_format((float) $row['highest_package']) ?>/yr<?php endif; ?>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#deptStatModal"
                        onclick='openResourceModal("deptStatModal", "<?= url('/dashboard/college/department-stats/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/college/department-stats/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this entry?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Alumni wall -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2 text-primary"></i>Alumni Wall</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#alumniModal" onclick="resetResourceModal('alumniModal', '<?= url('/dashboard/college/alumni') ?>')">Add</button>
    </div>
    <div class="card-body">
        <p class="text-muted small">These entries are college-reported and shown publicly labeled as such.</p>
        <?php if (empty($alumni)): ?>
            <p class="text-muted small mb-0">No alumni added yet.</p>
        <?php endif; ?>
        <?php foreach ($alumni as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start">
                <div>
                    <strong><?= e($row['name']) ?></strong><?= !empty($row['batch_year']) ? ' (' . e($row['batch_year']) . ')' : '' ?><br>
                    <span class="small text-muted">
                        <?= e($row['current_position'] ?? '') ?><?= !empty($row['current_company']) ? ' at ' . e($row['current_company']) : '' ?>
                        <?php if (!empty($row['department'])): ?> &middot; <?= e($row['department']) ?><?php endif; ?>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#alumniModal"
                        onclick='openResourceModal("alumniModal", "<?= url('/dashboard/college/alumni/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/college/alumni/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this entry?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php Core\View::partial('dashboard/college/_profile_modals'); ?>

<script src="<?= asset('js/profile.js') ?>"></script>
