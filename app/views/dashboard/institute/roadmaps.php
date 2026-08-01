<h1 class="h4 mb-1">Roadmaps</h1>
<p class="text-muted mb-4">Publish step-by-step learning roadmaps, optionally linking milestones to your own courses.</p>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-signpost-2 me-2 text-primary"></i>New roadmap</span>
    </div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/institute/roadmaps') ?>" class="row g-2 align-items-end" data-guard-submit>
            <?= csrf_field() ?>
            <div class="col-md-4">
                <label class="form-label small" for="roadmaps-title">Title</label>
                <input id="roadmaps-title" type="text" name="title" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small" for="roadmaps-description">Description</label>
                <input id="roadmaps-description" type="text" name="description" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Create</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($roadmaps)): ?>
    <p class="text-muted small">No roadmaps created yet.</p>
<?php endif; ?>

<?php foreach ($roadmaps as $roadmap): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><?= e($roadmap['title']) ?></span>
            <form method="post" action="<?= url('/dashboard/institute/roadmaps/' . $roadmap['id'] . '/delete') ?>" onsubmit="return confirm('Delete this roadmap?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete Roadmap</button>
            </form>
        </div>
        <div class="card-body">
            <?php if (!empty($roadmap['description'])): ?><p class="small text-muted"><?= e($roadmap['description']) ?></p><?php endif; ?>

            <?php foreach ($roadmap['milestones'] as $i => $milestone): ?>
                <div class="profile-row d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?= $i + 1 ?>. <?= e($milestone['title']) ?></strong>
                        <?php if (!empty($milestone['course_title'])): ?><span class="badge text-bg-light border ms-1"><?= e($milestone['course_title']) ?></span><?php endif; ?>
                        <?php if (!empty($milestone['description'])): ?><p class="small text-muted mb-0 mt-1"><?= e($milestone['description']) ?></p><?php endif; ?>
                    </div>
                    <form method="post" action="<?= url('/dashboard/institute/roadmaps/milestones/' . $milestone['id'] . '/delete') ?>" onsubmit="return confirm('Remove this milestone?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>

            <form method="post" action="<?= url('/dashboard/institute/roadmaps/' . $roadmap['id'] . '/milestones') ?>" class="row g-2 align-items-end mt-3" data-guard-submit>
                <?= csrf_field() ?>
                <div class="col-md-3">
                    <label class="form-label small" for="roadmaps-title-2">Milestone title</label>
                    <input id="roadmaps-title-2" type="text" name="title" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small" for="roadmaps-description-2">Description</label>
                    <input id="roadmaps-description-2" type="text" name="description" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small" for="roadmaps-course-id">Link to course (optional)</label>
                    <select id="roadmaps-course-id" name="course_id" class="form-select form-select-sm">
                        <option value="">None</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>"><?= e($course['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Add Step</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
