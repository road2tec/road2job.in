<h1 class="h4 mb-1">Colleges</h1>
<p class="text-muted mb-4">Read-only directory of registered colleges.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/colleges') ?>" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Search college name, owner name or email" value="<?= e($keyword) ?>">
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
                <tr><th>College</th><th>Owner</th><th>Location</th><th>Created</th></tr>
            </thead>
            <tbody>
                <?php if (empty($colleges)): ?>
                    <tr><td colspan="4" class="text-muted small">No colleges yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($colleges as $row): ?>
                    <tr>
                        <td><?= e($row['name'] ?? '(unnamed)') ?></td>
                        <td><span class="avatar-initials"><?= e(initials($row['owner_name'])) ?></span><?= e($row['owner_name']) ?><br><span class="small text-muted"><?= e($row['owner_email']) ?></span></td>
                        <td><?= e($row['location'] ?? '') ?></td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
