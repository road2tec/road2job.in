<?php
$typeLabels = [
    'full_time' => 'Full-time',
    'part_time' => 'Part-time',
    'internship' => 'Internship',
    'contract' => 'Contract',
    'remote' => 'Remote',
];
$experienceLabels = [
    'fresher' => 'Fresher',
    'junior' => 'Junior',
    'mid' => 'Mid-level',
    'senior' => 'Senior',
];
?>
<h1 class="h4 mb-1">Job Alerts</h1>
<p class="text-muted mb-4">Get notified the moment a job matching your criteria is posted.</p>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-bell me-2 text-primary"></i>New alert</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/job-alerts') ?>" data-guard-submit>
            <?= csrf_field() ?>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small" for="job-alerts-keyword">Keyword</label>
                    <input id="job-alerts-keyword" type="text" name="keyword" class="form-control" placeholder="e.g. PHP developer">
                </div>
                <div class="col-md-4">
                    <label class="form-label small" for="job-alerts-location">Location</label>
                    <input id="job-alerts-location" type="text" name="location" class="form-control" placeholder="e.g. Pune">
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="job-alerts-type">Type</label>
                    <select id="job-alerts-type" name="type" class="form-select">
                        <option value="">Any</option>
                        <?php foreach ($typeLabels as $value => $label): ?>
                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="job-alerts-experience-level">Experience</label>
                    <select id="job-alerts-experience-level" name="experience_level" class="form-select">
                        <option value="">Any</option>
                        <?php foreach ($experienceLabels as $value => $label): ?>
                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-check mt-2">
                <input type="checkbox" name="is_remote" value="1" class="form-check-input" id="alertRemote">
                <label class="form-check-label small" for="alertRemote">Remote only</label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm mt-2">Save alert</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-list-check me-2 text-primary"></i>Your alerts</div>
    <div class="card-body">
        <?php if (empty($alerts)): ?>
            <p class="text-muted small mb-0">No alerts saved yet.</p>
        <?php endif; ?>
        <?php foreach ($alerts as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small">
                    <?php if (!empty($row['keyword'])): ?><span class="badge text-bg-light border">"<?= e($row['keyword']) ?>"</span><?php endif; ?>
                    <?php if (!empty($row['location'])): ?><span class="badge text-bg-light border"><?= e($row['location']) ?></span><?php endif; ?>
                    <?php if (!empty($row['type'])): ?><span class="badge text-bg-light border"><?= e($typeLabels[$row['type']] ?? $row['type']) ?></span><?php endif; ?>
                    <?php if (!empty($row['experience_level'])): ?><span class="badge text-bg-light border"><?= e($experienceLabels[$row['experience_level']] ?? $row['experience_level']) ?></span><?php endif; ?>
                    <?php if (!empty($row['is_remote'])): ?><span class="badge text-bg-light border">Remote only</span><?php endif; ?>
                </div>
                <form method="post" action="<?= url('/dashboard/job-alerts/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Remove this alert?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
