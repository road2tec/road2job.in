<h1 class="h4 mb-1">Reports</h1>
<p class="text-muted mb-4">Download platform data as CSV. For visual charts, see <a href="<?= url('/dashboard/analytics') ?>">Analytics</a>.</p>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Institutes</div><div class="h5 mb-0"><?= (int) $instituteCount ?></div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Colleges</div><div class="h5 mb-0"><?= (int) $collegeCount ?></div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Companies</div><div class="h5 mb-0"><?= (int) $companyCount ?></div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Real hires (jobs + drives)</div><div class="h5 mb-0"><?= (int) (($applicationFunnel['selected'] ?? 0) + ($driveRegistrationFunnel['selected'] ?? 0)) ?></div></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Downloadable reports</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>Report</th><th></th></tr></thead>
            <tbody>
                <?php $reportLabels = ['users' => 'All Users', 'companies' => 'All Companies', 'jobs' => 'All Job Postings', 'applications' => 'Recent Applications (up to 200)']; ?>
                <?php foreach ($types as $type): ?>
                    <tr>
                        <td><?= e($reportLabels[$type] ?? ucfirst($type)) ?></td>
                        <td><a href="<?= url('/admin/reports/export/' . $type) ?>" class="btn btn-sm btn-outline-primary">Download CSV</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
