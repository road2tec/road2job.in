<h1 class="h4 mb-1">Settings</h1>
<p class="text-muted mb-4">Platform-wide configuration.</p>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('/admin/settings') ?>">
            <?= csrf_field() ?>
            <div class="mb-2">
                <label class="form-label small" for="settings-site-name">Site name</label>
                <input id="settings-site-name" type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? '') ?>">
            </div>
            <div class="mb-2">
                <label class="form-label small" for="settings-support-email">Support email</label>
                <input id="settings-support-email" type="email" name="support_email" class="form-control" value="<?= e($settings['support_email'] ?? '') ?>">
            </div>
            <?php if ($user['role_slug'] === 'super_admin'): ?>
                <p class="small text-muted mb-2">Maintenance mode moved to <a href="<?= url('/admin/system') ?>">System</a>.</p>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
