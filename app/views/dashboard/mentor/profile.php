<h1 class="h4 mb-1">Mentor Profile</h1>
<p class="text-muted mb-4">This information appears on your public mentor listing.</p>

<div class="card">
    <div class="card-header"><i class="bi bi-person-badge me-2 text-primary"></i>Profile details</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/mentor') ?>" data-guard-submit>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="profile-expertise">Areas of expertise</label>
                <input id="profile-expertise" type="text" name="expertise" class="form-control" placeholder="e.g. Web Development, Career Guidance, Resume Review" value="<?= e($mentor['expertise'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="profile-bio">Bio</label>
                <textarea id="profile-bio" name="bio" class="form-control" rows="4"><?= e($mentor['bio'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="profile-availability-note">Availability note</label>
                <input id="profile-availability-note" type="text" name="availability_note" class="form-control" placeholder="e.g. Weekday evenings" value="<?= e($mentor['availability_note'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </div>
</div>
