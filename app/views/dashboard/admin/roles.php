<h1 class="h4 mb-1">Roles</h1>
<p class="text-muted mb-4">The platform's fixed set of roles and how many accounts hold each one. Road2Job uses whole-role access control (no per-role permission editor) - each route is gated to specific role slugs.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr><th>Role</th><th>Self-registerable</th><th>Accounts</th></tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $row): ?>
                    <tr>
                        <td><?= e($row['label']) ?> <span class="text-muted small">(<?= e($row['slug']) ?>)</span></td>
                        <td><?= $row['self_registerable'] ? 'Yes' : 'No - assigned manually' ?></td>
                        <td><?= (int) $row['count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
