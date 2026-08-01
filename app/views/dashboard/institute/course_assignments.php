<h1 class="h4 mb-1">Assignments &amp; Projects</h1>
<p class="text-muted mb-4">for <strong><?= e($course['title']) ?></strong></p>

<p class="mb-3"><a href="<?= url('/dashboard/institute/courses') ?>">&larr; Back to courses</a></p>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-task me-2 text-primary"></i>Assignments</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignmentModal" onclick="resetResourceModal('assignmentModal', '<?= url('/dashboard/institute/courses/' . $course['id'] . '/assignments') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($assignments)): ?>
            <p class="text-muted small mb-0">No assignments added yet.</p>
        <?php endif; ?>
        <?php foreach ($assignments as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['title']) ?></strong>
                    <span class="badge text-bg-light border ms-1"><?= e(ucfirst($row['type'])) ?></span>
                    <br>
                    <?php if (!empty($row['due_date'])): ?><span class="small text-muted">Due <?= e($row['due_date']) ?></span><?php endif; ?>
                </div>
                <div class="d-flex gap-1">
                    <a href="<?= url('/dashboard/institute/assignments/' . $row['id'] . '/submissions') ?>" class="btn btn-sm btn-outline-primary">Submissions</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#assignmentModal"
                        onclick='openResourceModal("assignmentModal", "<?= url('/dashboard/institute/assignments/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/institute/assignments/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this assignment?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Assignment modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Assignment / Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="course-assignments-type">Type</label>
                            <select id="course-assignments-type" name="type" class="form-select">
                                <option value="assignment">Assignment</option>
                                <option value="project">Project</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="course-assignments-due-date">Due date</label>
                            <input id="course-assignments-due-date" type="date" name="due_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="course-assignments-title">Title</label><input id="course-assignments-title" type="text" name="title" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="course-assignments-description">Description / instructions</label><textarea id="course-assignments-description" name="description" class="form-control" rows="4"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/profile.js') ?>"></script>
