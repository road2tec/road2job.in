<h1 class="h4 mb-1">Audit Logs</h1>
<p class="text-muted mb-4">Full trail of authentication and admin actions.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/audit-logs') ?>" class="row g-2">
            <div class="col-md-8">
                <input type="text" name="keyword" class="form-control" placeholder="Filter by action (e.g. admin_user, login, admin_blog)" value="<?= e($keyword) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>User</th><th>Action</th><th>Description</th><th>IP</th><th>When</th></tr></thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" class="text-muted small">No matching log entries.</td></tr>
                <?php endif; ?>
                <?php foreach ($logs as $row): ?>
                    <tr>
                        <td><?= e($row['actor_name'] ?? 'System') ?></td>
                        <td><span class="badge text-bg-light border"><?= e($row['action']) ?></span></td>
                        <td class="small text-muted"><?= e($row['description']) ?></td>
                        <td class="small text-muted"><?= e($row['ip_address']) ?></td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
