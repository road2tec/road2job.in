<h1 class="h4 mb-1">Institutes</h1>
<p class="text-muted mb-4">Directory of registered training institutes - moderate visibility and verification here.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/institutes') ?>" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Search institute name, owner name or email" value="<?= e($keyword) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr><th>Institute</th><th>Owner</th><th>Location</th><th>Rank</th><th>Status</th><th>Verification</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($institutes)): ?>
                    <tr><td colspan="7" class="text-muted small">No institutes yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($institutes as $row): ?>
                    <tr>
                        <td><?= e($row['name'] ?? '(unnamed)') ?></td>
                        <td><span class="avatar-initials"><?= e(initials($row['owner_name'])) ?></span><?= e($row['owner_name']) ?><br><span class="small text-muted"><?= e($row['owner_email']) ?></span></td>
                        <td><?= e($row['location'] ?? '') ?></td>
                        <td class="small text-muted"><?= number_format((float) ($row['rank_score'] ?? 0), 1) ?></td>
                        <td>
                            <form method="post" action="<?= url('/admin/institutes/' . $row['id'] . '/status') ?>">
                                <?= csrf_field() ?>
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Update status for <?= e($row['name'] ?? '') ?>">
                                    <?php foreach (['active' => 'Active', 'deactivated' => 'Deactivated'] as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($row['status'] ?? 'active') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="<?= url('/admin/institutes/' . $row['id'] . '/verification') ?>">
                                <?= csrf_field() ?>
                                <select name="verification_status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Update verification for <?= e($row['name'] ?? '') ?>">
                                    <?php foreach (['unverified' => 'Unverified', 'verified' => 'Verified'] as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($row['verification_status'] ?? 'unverified') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <?php if (!empty($row['name'])): ?>
                                <a href="<?= url('/institutes/' . $row['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
