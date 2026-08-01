<h1 class="h4 mb-1">Interviews</h1>
<p class="text-muted mb-4">Recorded interviews requested from your applicants.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-camera-video me-2 text-primary"></i>Interview sessions</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr><td colspan="5" class="text-muted small">No interviews requested yet. Request one from your <a href="<?= url('/dashboard/applicants') ?>">Applicants</a> list.</td></tr>
                <?php endif; ?>
                <?php foreach ($sessions as $row): ?>
                    <?php $statusBadge = ['pending' => 'text-bg-secondary', 'in_progress' => 'text-bg-info', 'completed' => 'text-bg-success'][$row['status']] ?? 'text-bg-secondary'; ?>
                    <tr>
                        <td><?= e($row['student_name']) ?><br><span class="small text-muted"><?= e($row['student_email']) ?></span></td>
                        <td><?= e($row['job_title']) ?></td>
                        <td><span class="badge <?= $statusBadge ?>"><?= e(ucfirst(str_replace('_', ' ', $row['status']))) ?></span></td>
                        <td class="small text-muted"><?= e($row['requested_at']) ?></td>
                        <td><a href="<?= url('/dashboard/interviews/' . $row['id']) ?>" class="btn btn-sm btn-outline-secondary">Review</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
