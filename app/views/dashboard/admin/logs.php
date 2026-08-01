<h1 class="h4 mb-1">Error Logs</h1>
<p class="text-muted mb-4">Most recent <?= (int) $retentionDays ?> days are kept manually - this server has no scheduled task runner, so cleanup is triggered on demand.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/logs') ?>" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small" for="logs-date">Log date</label>
                <select id="logs-date" name="date" class="form-select" onchange="this.form.submit()">
                    <?php if (empty($availableDates)): ?>
                        <option value="">No log files yet</option>
                    <?php endif; ?>
                    <?php foreach ($availableDates as $date): ?>
                        <option value="<?= e($date) ?>" <?= $date === $selectedDate ? 'selected' : '' ?>><?= e($date) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Log: <?= e($selectedDate) ?> (most recent first, up to 500 lines)</div>
    <div class="card-body">
        <?php if (empty($lines)): ?>
            <p class="text-muted small mb-0">No log entries for this date.</p>
        <?php else: ?>
            <pre class="small mb-0" style="max-height:500px;overflow-y:auto;white-space:pre-wrap;"><?php foreach ($lines as $line): ?><?= e($line) ?>
<?php endforeach; ?></pre>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= url('/admin/logs/cleanup') ?>" onsubmit="return confirm('Delete all log files older than <?= (int) $retentionDays ?> days?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete logs older than <?= (int) $retentionDays ?> days</button>
        </form>
    </div>
</div>
