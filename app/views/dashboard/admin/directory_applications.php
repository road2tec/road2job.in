<?php
$statusBadge = ['applied' => 'text-bg-secondary', 'under_review' => 'text-bg-info', 'shortlisted' => 'text-bg-primary', 'rejected' => 'text-bg-danger', 'selected' => 'text-bg-success'];
?>
<h1 class="h4 mb-1">Applications</h1>
<p class="text-muted mb-4">Read-only oversight of job applications platform-wide.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/applications') ?>" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Search job title, company, student name or email" value="<?= e($keyword) ?>">
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
                <tr><th>Job</th><th>Company</th><th>Student</th><th>Status</th><th>Applied</th></tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="5" class="text-muted small">No applications yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($applications as $row): ?>
                    <tr>
                        <td><?= e($row['job_title']) ?></td>
                        <td><?= e($row['company_name']) ?></td>
                        <td><span class="avatar-initials"><?= e(initials($row['student_name'])) ?></span><?= e($row['student_name']) ?><br><span class="small text-muted"><?= e($row['student_email']) ?></span></td>
                        <td><span class="badge <?= $statusBadge[$row['status']] ?? 'text-bg-secondary' ?>"><?= e(ucfirst(str_replace('_', ' ', $row['status']))) ?></span></td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
