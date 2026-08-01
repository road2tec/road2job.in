<h1 class="h4 mb-1">Security</h1>
<p class="text-muted mb-4">Manage where you're logged in and review recent login activity.</p>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-laptop me-2 text-primary"></i>Active sessions</span>
        <?php if (count($sessions) > 1): ?>
            <form method="post" action="<?= url('/account/security/revoke-all') ?>" onsubmit="return confirm('Log out all other devices?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">Log out all other devices</button>
            </form>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>IP address</th>
                    <th>Signed in</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $s): ?>
                    <?php $isCurrent = $currentSessionRecordId !== null && (int) $s['id'] === (int) $currentSessionRecordId; ?>
                    <tr>
                        <td>
                            <?= e($s['user_agent'] ?: 'Unknown device') ?>
                            <?php if ($isCurrent): ?>
                                <span class="badge text-bg-success ms-1">This device</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($s['ip_address']) ?></td>
                        <td><?= e($s['login_at']) ?></td>
                        <td class="text-end">
                            <?php if (!$isCurrent): ?>
                                <form method="post" action="<?= url('/account/security/revoke/' . $s['id']) ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Sign out</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clock-history me-2 text-primary"></i>Recent login history</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>When</th>
                    <th>IP address</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($loginHistory)): ?>
                    <tr><td colspan="3" class="text-muted small">No login history yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($loginHistory as $attempt): ?>
                    <tr>
                        <td><?= e($attempt['attempted_at']) ?></td>
                        <td><?= e($attempt['ip_address']) ?></td>
                        <td>
                            <?php if ((int) $attempt['success'] === 1): ?>
                                <span class="badge text-bg-success">Success</span>
                            <?php else: ?>
                                <span class="badge text-bg-danger">Failed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
