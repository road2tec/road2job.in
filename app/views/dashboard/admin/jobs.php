<?php
$statusBadge = ['draft' => 'text-bg-secondary', 'published' => 'text-bg-success', 'closed' => 'text-bg-danger'];
?>
<h1 class="h4 mb-1">Jobs</h1>
<p class="text-muted mb-4">Moderate job postings platform-wide.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/jobs') ?>" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="keyword" class="form-control" placeholder="Search title" value="<?= e($filters['keyword']) ?>">
            </div>
            <div class="col-md-4">
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
                <tr><th>Title</th><th>Company</th><th>Created</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr><td colspan="5" class="text-muted small">No job postings match this filter.</td></tr>
                <?php endif; ?>
                <?php foreach ($jobs as $row): ?>
                    <tr>
                        <td><?= e($row['title']) ?></td>
                        <td><?= e($row['company_name']) ?></td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                        <td>
                            <form method="post" action="<?= url('/admin/jobs/' . $row['id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block;" onchange="this.form.submit()" aria-label="Update status for <?= e($row['title']) ?>">
                                    <?php foreach (['draft', 'published', 'closed'] as $statusOption): ?>
                                        <option value="<?= e($statusOption) ?>" <?= $row['status'] === $statusOption ? 'selected' : '' ?>><?= e(ucfirst($statusOption)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="<?= url('/admin/jobs/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this job posting?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
