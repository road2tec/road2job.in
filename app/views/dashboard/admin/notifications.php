<h1 class="h4 mb-1">Notifications</h1>
<p class="text-muted mb-4">Broadcast a notification to all users, or to a specific role.</p>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('/admin/notifications') ?>">
            <?= csrf_field() ?>
            <div class="mb-2">
                <label class="form-label small" for="notifications-role">Audience</label>
                <select id="notifications-role" name="role" class="form-select">
                    <option value="">All users</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e($role) ?>"><?= e(ucfirst($role)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label small" for="notifications-title">Title</label>
                <input id="notifications-title" type="text" name="title" class="form-control" required maxlength="150">
            </div>
            <div class="mb-2">
                <label class="form-label small" for="notifications-message">Message</label>
                <textarea id="notifications-message" name="message" class="form-control" rows="3" required maxlength="255"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Broadcast</button>
        </form>
    </div>
</div>
