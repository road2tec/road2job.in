<h1 class="h4 mb-1">Courses</h1>
<p class="text-muted mb-4">Create and manage the courses your institute offers.</p>

<?php if ($institute === null): ?>
    <div class="alert alert-warning">
        Please <a href="<?= url('/dashboard/institute') ?>">set up your institute profile</a> before adding a course.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-mortarboard me-2 text-primary"></i>Your courses</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal" onclick="resetResourceModal('courseModal', '<?= url('/dashboard/institute/courses') ?>')" <?= $institute === null ? 'disabled' : '' ?>>Add course</button>
    </div>
    <div class="card-body">
        <?php if (empty($courses)): ?>
            <p class="text-muted small mb-0">No courses added yet.</p>
        <?php endif; ?>
        <?php foreach ($courses as $row): ?>
            <?php $statusBadge = ['draft' => 'text-bg-secondary', 'published' => 'text-bg-success', 'closed' => 'text-bg-danger'][$row['status']] ?? 'text-bg-secondary'; ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['title']) ?></strong>
                    <span class="badge <?= $statusBadge ?> ms-1"><?= e(ucfirst($row['status'])) ?></span>
                    <br>
                    <span class="badge text-bg-light border ms-1"><?= e(ucfirst($row['format'])) ?></span>
                    <br>
                    <span class="small text-muted">
                        <?= e(ucfirst($row['mode'])) ?>
                        <?php if (!empty($row['duration'])): ?> &middot; <?= e($row['duration']) ?><?php endif; ?>
                        <?php if (!empty($row['fee'])): ?> &middot; &#8377;<?= number_format((float) $row['fee']) ?><?php endif; ?>
                        <?php if (!empty($row['start_date'])): ?> &middot; Starts <?= e($row['start_date']) ?><?php endif; ?>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <a href="<?= url('/dashboard/institute/courses/' . $row['id'] . '/assignments') ?>" class="btn btn-sm btn-outline-primary">Assignments</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#courseModal"
                        onclick='openResourceModal("courseModal", "<?= url('/dashboard/institute/courses/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/institute/courses/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this course?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Course modal -->
<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="courses-title">Course title</label><input id="courses-title" type="text" name="title" class="form-control" required></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label" for="courses-format">Format</label>
                            <select id="courses-format" name="format" class="form-select">
                                <option value="course">Course</option>
                                <option value="bootcamp">Bootcamp</option>
                                <option value="workshop">Workshop</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="courses-mode">Mode</label>
                            <select id="courses-mode" name="mode" class="form-select">
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="courses-duration">Duration</label>
                            <input id="courses-duration" type="text" name="duration" class="form-control" placeholder="e.g. 6 months">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="courses-status">Status</label>
                            <select id="courses-status" name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label" for="courses-start-date">Start date (bootcamp/workshop)</label>
                            <input id="courses-start-date" type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="courses-end-date">End date (bootcamp/workshop)</label>
                            <input id="courses-end-date" type="date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="courses-fee">Fee (&#8377;)</label>
                            <input id="courses-fee" type="number" name="fee" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="courses-description">Description</label><textarea id="courses-description" name="description" class="form-control" rows="3"></textarea></div>
                    <div class="mb-2"><label class="form-label" for="courses-syllabus-highlights">Syllabus highlights</label><textarea id="courses-syllabus-highlights" name="syllabus_highlights" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/profile.js') ?>"></script>
