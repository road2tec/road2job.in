<h1 class="h4 mb-1">My Learning</h1>
<p class="text-muted mb-4">Courses, bootcamps and workshops you're enrolled in.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-mortarboard me-2 text-primary"></i>Enrollments</div>
    <div class="card-body">
        <?php if (empty($enrollments)): ?>
            <p class="text-muted small mb-0">You're not enrolled in anything yet. Browse <a href="<?= url('/institutes') ?>">institutes</a> to find a course.</p>
        <?php endif; ?>
        <?php foreach ($enrollments as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['course_title']) ?></strong>
                    <span class="badge text-bg-light border ms-1"><?= e(ucfirst($row['course_format'])) ?></span>
                    <?php if ($row['status'] === 'completed'): ?><span class="badge text-bg-success ms-1">Completed</span><?php endif; ?>
                    <br>
                    <span class="small text-muted"><?= e($row['institute_name']) ?></span>
                </div>
                <div class="d-flex gap-1">
                    <a href="<?= url('/dashboard/my-learning/' . $row['course_id']) ?>" class="btn btn-sm btn-primary">Open</a>
                    <?php if ($row['status'] === 'completed'): ?>
                        <a href="<?= url('/dashboard/my-learning/' . $row['course_id'] . '/certificate') ?>" target="_blank" class="btn btn-sm btn-outline-success">Certificate</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
