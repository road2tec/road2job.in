<h1 class="h4 mb-1">Institute Updates</h1>
<p class="text-muted mb-4">Post placement drives, admissions, workshops and achievements - recent, genuine updates strengthen your discovery ranking.</p>

<?php if ($institute === null): ?>
    <div class="alert alert-warning">
        Please <a href="<?= url('/dashboard/institute') ?>">set up your institute profile</a> before posting an update.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-megaphone me-2 text-primary"></i>Your updates</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal" onclick="resetResourceModal('updateModal', '<?= url('/dashboard/institute/updates') ?>')" <?= $institute === null ? 'disabled' : '' ?>>Post update</button>
    </div>
    <div class="card-body">
        <?php if (empty($updates)): ?>
            <p class="text-muted small mb-0">No updates posted yet.</p>
        <?php endif; ?>
        <?php $categoryLabels = ['announcement' => 'Announcement', 'placement_news' => 'Placement News', 'achievement' => 'Achievement', 'event' => 'Event', 'course_update' => 'Course Update', 'general' => 'General']; ?>
        <?php foreach ($updates as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <span class="badge text-bg-light border"><?= e($categoryLabels[$row['category']] ?? $row['category']) ?></span>
                    <?php if (($row['status'] ?? 'active') !== 'active'): ?><span class="badge text-bg-secondary">Hidden by admin</span><?php endif; ?>
                    <br>
                    <strong><?= e($row['title']) ?></strong>
                    <p class="small text-muted mb-0"><?= e(mb_strimwidth($row['body'], 0, 160, '...')) ?></p>
                    <span class="small text-muted"><?= e(display_date($row['created_at'] ?? '', '')) ?></span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateModal"
                        onclick='openResourceModal("updateModal", "<?= url('/dashboard/institute/updates/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/institute/updates/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this update?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Update modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Institute Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" for="updates-category">Category</label>
                        <select id="updates-category" name="category" class="form-select">
                            <option value="general">General</option>
                            <option value="announcement">Announcement</option>
                            <option value="placement_news">Placement News</option>
                            <option value="achievement">Achievement</option>
                            <option value="event">Event</option>
                            <option value="course_update">Course Update</option>
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label" for="updates-title">Title</label><input id="updates-title" type="text" name="title" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="updates-body">Details</label><textarea id="updates-body" name="body" class="form-control" rows="4" required></textarea></div>
                    <div class="mb-2">
                        <label class="form-label" for="updates-image">Image (optional)</label>
                        <input id="updates-image" type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/profile.js') ?>"></script>
