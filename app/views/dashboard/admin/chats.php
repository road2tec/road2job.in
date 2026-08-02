<h1 class="h4 mb-1">Chats</h1>
<p class="text-muted mb-4">Employer-student conversations - review and moderate here.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/chats') ?>" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Search employer, student, or company name" value="<?= e($keyword) ?>">
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
                <tr><th>Employer</th><th>Student</th><th>Messages</th><th>Last activity</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($threads)): ?>
                    <tr><td colspan="6" class="text-muted small">No conversations yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($threads as $row): ?>
                    <tr>
                        <td><?= e($row['employer_name']) ?><?php if (!empty($row['company_name'])): ?><br><span class="small text-muted"><?= e($row['company_name']) ?></span><?php endif; ?></td>
                        <td><?= e($row['student_name']) ?></td>
                        <td><?= (int) $row['message_count'] ?></td>
                        <td class="small text-muted"><?= e($row['last_message_at'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $row['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst($row['status'])) ?></span>
                        </td>
                        <td><a href="<?= url('/admin/chats/' . $row['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
