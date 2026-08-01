<?php
$verificationBadge = ['unverified' => 'text-bg-secondary', 'pending' => 'text-bg-warning', 'verified' => 'text-bg-success', 'rejected' => 'text-bg-danger'];
$companyRowAccent = ['rejected' => 'row-attn-danger', 'pending' => 'row-attn-warning', 'unverified' => 'row-attn-warning'];
?>
<h1 class="h4 mb-1">Companies</h1>
<p class="text-muted mb-4">Employer company profiles and verification.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/companies') ?>" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Search company name, owner name or email" value="<?= e($keyword) ?>">
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
                <tr><th>Company</th><th>Owner</th><th>Created</th><th>Verification</th></tr>
            </thead>
            <tbody>
                <?php if (empty($companies)): ?>
                    <tr><td colspan="4" class="text-muted small">No companies yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($companies as $row): ?>
                    <tr class="<?= e($companyRowAccent[$row['verification_status']] ?? '') ?>">
                        <td><?= e($row['name'] ?? '(unnamed)') ?></td>
                        <td><span class="avatar-initials"><?= e(initials($row['owner_name'])) ?></span><?= e($row['owner_name']) ?><br><span class="small text-muted"><?= e($row['owner_email']) ?></span></td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                        <td>
                            <form method="post" action="<?= url('/admin/companies/' . $row['id']) ?>" class="d-flex gap-1 align-items-center">
                                <?= csrf_field() ?>
                                <span class="badge <?= $verificationBadge[$row['verification_status']] ?? 'text-bg-secondary' ?>"><?= e(ucfirst($row['verification_status'])) ?></span>
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Update verification status for <?= e($row['name'] ?? 'this company') ?>">
                                    <?php foreach (['unverified', 'pending', 'verified', 'rejected'] as $statusOption): ?>
                                        <option value="<?= e($statusOption) ?>" <?= $row['verification_status'] === $statusOption ? 'selected' : '' ?>><?= e(ucfirst($statusOption)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
