<h1 class="h4 mb-1">Settings</h1>
<p class="text-muted mb-4">Manage your password, visibility, and notification preferences.</p>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-key me-2 text-primary"></i>Change password</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/settings/password') ?>" class="row g-3" data-guard-submit>
            <?= csrf_field() ?>
            <div class="col-md-4">
                <label class="form-label" for="currentPassword">Current password</label>
                <div class="input-group">
                    <input type="password" name="current_password" id="currentPassword" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-password="#currentPassword" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="newPassword">New password</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="newPassword" class="form-control" minlength="8" required>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-password="#newPassword" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="newPasswordConfirm">Confirm new password</label>
                <div class="input-group">
                    <input type="password" name="new_password_confirmation" id="newPasswordConfirm" class="form-control" minlength="8" required data-match-target="#newPassword" data-match-feedback="#settingsMatchFeedback">
                    <button class="btn btn-outline-secondary" type="button" data-toggle-password="#newPasswordConfirm" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="settingsMatchFeedback" class="form-text"></div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" data-loading-text="Updating...">Update password</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-sliders me-2 text-primary"></i>Preferences</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/settings/preferences') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="settings-username">Username</label>
                <div class="input-group" style="max-width: 420px;">
                    <span class="input-group-text"><?= e(url('/u/')) ?></span>
                    <input id="settings-username" type="text" name="username" class="form-control" value="<?= e($account['username'] ?? '') ?>" pattern="[a-z0-9-]{3,50}" placeholder="your-username">
                </div>
                <div class="form-text">Lowercase letters, numbers and hyphens only. This is your <a href="<?= url('/dashboard/portfolio') ?>">portfolio</a> URL.</div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="settings-profile-visibility">Profile visibility</label>
                <select id="settings-profile-visibility" name="profile_visibility" class="form-select" style="max-width: 300px;">
                    <option value="private" <?= ($profile['profile_visibility'] ?? 'private') === 'private' ? 'selected' : '' ?>>Private</option>
                    <option value="public" <?= ($profile['profile_visibility'] ?? 'private') === 'public' ? 'selected' : '' ?>>Public</option>
                </select>
                <div class="form-text">Public profiles are visible to anyone with your portfolio link.</div>
            </div>

            <div class="form-check mb-2">
                <input type="checkbox" name="email_notifications" value="1" class="form-check-input" id="emailNotif" <?= (($profile['email_notifications'] ?? 1)) ? 'checked' : '' ?>>
                <label class="form-check-label" for="emailNotif">Email notifications</label>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="sms_notifications" value="1" class="form-check-input" id="smsNotif" <?= (($profile['sms_notifications'] ?? 1)) ? 'checked' : '' ?>>
                <label class="form-check-label" for="smsNotif">SMS notifications</label>
            </div>

            <button type="submit" class="btn btn-primary">Save preferences</button>
        </form>
    </div>
</div>
