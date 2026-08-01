<h1 class="h4 mb-1">System Health</h1>
<p class="text-muted mb-4">Real, self-contained signals only - for external uptime alerting, point a free service (e.g. UptimeRobot) at the homepage.</p>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Database</div>
                <div class="h6 mb-0">
                    <span class="badge <?= $dbHealthy ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $dbHealthy ? 'Connected' : 'Unreachable' ?></span>
                </div>
                <?php if (!$dbHealthy): ?>
                    <p class="small text-danger mb-0 mt-1"><?= e($dbError) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Errors (last 2 days)</div>
                <div class="h5 mb-0"><?= (int) $errorCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Disk Free</div>
                <div class="h5 mb-0"><?= $diskFreeGb !== null ? e($diskFreeGb) . ' GB' : 'Unknown' ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">PHP</div>
                <div class="h6 mb-0"><?= e($phpVersion) ?></div>
                <div class="small text-muted">memory_limit: <?= e($memoryLimit) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p class="small text-muted mb-0">Want the full error trail, not just a count? See <a href="<?= url('/admin/logs') ?>">Error Logs</a>.</p>
    </div>
</div>
