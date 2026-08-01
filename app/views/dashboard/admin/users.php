<?php $userRowAccent = ['suspended' => 'row-attn-danger', 'banned' => 'row-attn-danger', 'pending' => 'row-attn-warning']; ?>
<h1 class="h4 mb-1">Users</h1>
<p class="text-muted mb-4">All platform accounts.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/users') ?>" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="keyword" class="form-control" placeholder="Search name or email" value="<?= e($filters['keyword']) ?>">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select" aria-label="Filter by role">
                    <option value="">All roles</option>
                    <?php foreach (['student', 'employer', 'recruiter', 'institute', 'college', 'mentor', 'admin', 'super_admin'] as $role): ?>
                        <option value="<?= e($role) ?>" <?= $filters['role'] === $role ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $role))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    <?php foreach ($statuses as $statusOption): ?>
                        <option value="<?= e($statusOption) ?>" <?= $filters['status'] === $statusOption ? 'selected' : '' ?>><?= e(ucfirst($statusOption)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-muted small">No users match this filter.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $row): ?>
                    <tr class="<?= e($userRowAccent[$row['status']] ?? '') ?>">
                        <td><span class="avatar-initials"><?= e(initials($row['full_name'])) ?></span><?= e($row['full_name']) ?></td>
                        <td><?= e($row['email']) ?></td>
                        <td><span class="badge text-bg-light border"><?= e($row['role_label']) ?></span></td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                        <td>
                            <form method="post" action="<?= url('/admin/users/' . $row['id']) ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Update status for <?= e($row['full_name']) ?>">
                                    <?php foreach ($statuses as $statusOption): ?>
                                        <option value="<?= e($statusOption) ?>" <?= $row['status'] === $statusOption ? 'selected' : '' ?>><?= e(ucfirst($statusOption)) ?></option>
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
