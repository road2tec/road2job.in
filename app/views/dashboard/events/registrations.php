<h1 class="h4 mb-1">Registrations</h1>
<p class="text-muted mb-4">for <strong><?= e($event['title']) ?></strong></p>

<p class="mb-3"><a href="<?= url('/dashboard/events') ?>">&larr; Back to events</a></p>

<?php if (empty($registrations)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">No registrations yet.</p>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($registrations as $row): ?>
    <div class="profile-row d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong><?= e($row['registrant_name']) ?></strong>
            <span class="small text-muted d-block"><?= e($row['registrant_email']) ?> &middot; Registered <?= e($row['registered_at']) ?></span>
        </div>
        <div>
            <?php if ($row['status'] === 'attended'): ?>
                <span class="badge text-bg-success">Attended</span>
            <?php else: ?>
                <form method="post" action="<?= url('/dashboard/events/registrations/' . $row['id'] . '/attend') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-success">Mark attended</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
