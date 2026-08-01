<h1 class="h4 mb-1">Drive Registrations</h1>
<p class="text-muted mb-4">Students who've registered interest in your campus drives.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-person-check me-2 text-primary"></i>Registrations</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Drive</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="5" class="text-muted small">No registrations yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($requests as $row): ?>
                    <tr>
                        <td>
                            <?= e($row['student_name']) ?><br>
                            <span class="small text-muted"><?= e($row['student_email']) ?></span>
                        </td>
                        <td><?= e($row['drive_company_name']) ?></td>
                        <td class="small text-muted"><?= e($row['message'] ?? '') ?></td>
                        <td>
                            <form method="post" action="<?= url('/dashboard/college/registrations/' . $row['id']) ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Update registration status for <?= e($row['student_name']) ?>">
                                    <?php foreach (['pending', 'shortlisted', 'selected', 'rejected'] as $statusOption): ?>
                                        <option value="<?= $statusOption ?>" <?= $row['status'] === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td class="small text-muted"><?= e($row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
