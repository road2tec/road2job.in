<h1 class="h4 mb-1">Mock Interviews</h1>
<p class="text-muted mb-4">Self-serve practice interviews - record your answers, review them, and optionally request mentor feedback.</p>

<form method="post" action="<?= url('/dashboard/mock-interviews/start') ?>" class="mb-4">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Start new practice interview</button>
</form>

<div class="card">
    <div class="card-header"><i class="bi bi-camera-video me-2 text-primary"></i>Your sessions</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Started</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr><td colspan="3" class="text-muted small">No practice interviews yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($sessions as $row): ?>
                    <tr>
                        <td><span class="badge <?= $row['status'] === 'completed' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst($row['status'])) ?></span></td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                        <td><a href="<?= url('/dashboard/mock-interviews/' . $row['id']) ?>" class="btn btn-sm btn-primary"><?= $row['status'] === 'completed' ? 'View' : 'Continue' ?></a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
