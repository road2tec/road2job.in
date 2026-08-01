<h1 class="h4 mb-1">Enrollment Requests</h1>
<p class="text-muted mb-4">Students who've asked to enroll in your courses.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-person-check me-2 text-primary"></i>Requests</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="5" class="text-muted small">No enrollment requests yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($requests as $row): ?>
                    <tr>
                        <td>
                            <?= e($row['student_name']) ?><br>
                            <span class="small text-muted"><?= e($row['student_email']) ?></span>
                        </td>
                        <td><?= e($row['course_title']) ?></td>
                        <td class="small text-muted"><?= e($row['message'] ?? '') ?></td>
                        <td>
                            <form method="post" action="<?= url('/dashboard/institute/enrollments/' . $row['id']) ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" aria-label="Update enrollment status for <?= e($row['student_name']) ?>">
                                    <?php foreach (['pending', 'contacted', 'enrolled', 'declined'] as $statusOption): ?>
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
