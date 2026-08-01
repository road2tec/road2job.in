<?php
$statusBadge = ['draft' => 'text-bg-secondary', 'published' => 'text-bg-success', 'completed' => 'text-bg-primary'];
?>
<h1 class="h4 mb-1">Events</h1>
<p class="text-muted mb-4">Moderate events hosted platform-wide.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/events') ?>" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Search event title or organizer" value="<?= e($keyword) ?>">
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
                <tr><th>Title</th><th>Organizer</th><th>Category</th><th>Starts</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr><td colspan="6" class="text-muted small">No events yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($events as $row): ?>
                    <tr>
                        <td><?= e($row['title']) ?></td>
                        <td><?= e($row['organizer_name']) ?></td>
                        <td><span class="badge text-bg-light border"><?= e(ucwords(str_replace('-', ' ', $row['category']))) ?></span></td>
                        <td class="small text-muted"><?= e($row['starts_at']) ?></td>
                        <td><span class="badge <?= $statusBadge[$row['status']] ?? 'text-bg-secondary' ?>"><?= e(ucfirst($row['status'])) ?></span></td>
                        <td>
                            <form method="post" action="<?= url('/admin/events/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this event?');">
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
