<h1 class="h4 mb-1">Applicants</h1>
<p class="text-muted mb-4">Everyone who has applied to your job postings.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-people me-2 text-primary"></i>Applicants</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Job</th>
                    <th>Cover note</th>
                    <th>Status</th>
                    <th><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="5" class="text-muted small">No applicants yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($applications as $row): ?>
                    <tr>
                        <td>
                            <?= e($row['student_name']) ?><br>
                            <span class="small text-muted"><?= e($row['student_email']) ?></span>
                        </td>
                        <td><?= e($row['job_title']) ?></td>
                        <td class="small text-muted"><?= e($row['cover_note'] ?? '') ?></td>
                        <td>
                            <form method="post" action="<?= url('/dashboard/applicants/' . $row['id']) ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Update application status for <?= e($row['student_name']) ?>">
                                    <?php foreach (['applied', 'under_review', 'shortlisted', 'rejected', 'selected'] as $statusOption): ?>
                                        <option value="<?= $statusOption ?>" <?= $row['status'] === $statusOption ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $statusOption)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td class="d-flex gap-1">
                            <a href="<?= url('/dashboard/applicants/' . $row['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Resume</a>
                            <form method="post" action="<?= url('/dashboard/applicants/' . $row['id'] . '/request-interview') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-camera-video"></i> Interview</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
