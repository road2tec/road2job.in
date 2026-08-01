<h1 class="h4 mb-1">Security</h1>
<p class="text-muted mb-4">Platform-wide login activity and per-user session control.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/security') ?>" class="row g-2">
            <div class="col-md-8">
                <input type="text" name="keyword" class="form-control" placeholder="Search a user by name or email to force-logout their sessions" value="<?= e($keyword) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
    </div>
</div>

<?php if ($keyword !== ''): ?>
<div class="card mb-4">
    <div class="card-header">Search results</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>Name</th><th>Email</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($searchResults)): ?>
                    <tr><td colspan="3" class="text-muted small">No matching users.</td></tr>
                <?php endif; ?>
                <?php foreach ($searchResults as $row): ?>
                    <tr>
                        <td><span class="avatar-initials"><?= e(initials($row['full_name'])) ?></span><?= e($row['full_name']) ?></td>
                        <td><?= e($row['email']) ?></td>
                        <td>
                            <form method="post" action="<?= url('/admin/security/' . $row['id'] . '/revoke') ?>" onsubmit="return confirm('Force-logout all sessions for this user?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Force logout</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Recent activity</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>User</th><th>Action</th><th>Description</th><th>IP</th><th>When</th></tr></thead>
            <tbody>
                <?php if (empty($recentActivity)): ?>
                    <tr><td colspan="5" class="text-muted small">No activity recorded yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($recentActivity as $row): ?>
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
