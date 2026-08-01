<h1 class="h4 mb-1">System</h1>
<p class="text-muted mb-4">Read-only configuration status - values are set via the server's <code>.env</code> file, never shown or edited here.</p>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">SMS &amp; WhatsApp OTP <span class="badge text-bg-light border ms-1">channel: <?= e($otpChannel) ?></span></div>
            <div class="card-body">
                <?php foreach ($smsStatus as $label => $configured): ?>
                    <div class="d-flex justify-content-between align-items-center profile-row">
                        <span class="small"><?= e($label) ?></span>
                        <span class="badge <?= $configured ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $configured ? 'Configured' : 'Not set' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">SMTP</div>
            <div class="card-body">
                <?php foreach ($mailStatus as $label => $configured): ?>
                    <div class="d-flex justify-content-between align-items-center profile-row">
                        <span class="small"><?= e($label) ?></span>
                        <span class="badge <?= $configured ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $configured ? 'Configured' : 'Not set' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Maintenance Mode</div>
    <div class="card-body">
        <p class="small text-muted">When enabled, every visitor except logged-in admin/super admin accounts sees a maintenance page instead of the site. Use with care - this affects all live traffic immediately.</p>
        <form method="post" action="<?= url('/admin/system/maintenance-mode') ?>">
            <?= csrf_field() ?>
            <div class="form-check form-switch mb-3">
                <input type="checkbox" name="maintenance_mode" value="1" class="form-check-input" id="maintenanceModeToggle" <?= $maintenanceMode ? 'checked' : '' ?>>
                <label class="form-check-label" for="maintenanceModeToggle">Maintenance mode is currently <strong><?= $maintenanceMode ? 'ON' : 'OFF' ?></strong></label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </form>
    </div>
</div>
