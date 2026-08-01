<?php
$roleLabelsMap = ['student' => 'Student', 'employer' => 'Employer', 'recruiter' => 'Recruiter', 'institute' => 'Institute', 'college' => 'College', 'mentor' => 'Mentor', 'admin' => 'Admin', 'super_admin' => 'Super Admin'];
$roleChart = [];
foreach ($roleLabelsMap as $key => $label) {
    if (!empty($usersByRole[$key])) {
        $roleChart[$label] = $usersByRole[$key];
    }
}

$jobLabels = ['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'];
$jobChart = [];
foreach ($jobLabels as $key => $label) {
    $jobChart[$label] = $jobFunnel[$key] ?? 0;
}

$appLabels = ['applied' => 'Applied', 'under_review' => 'Under Review', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'selected' => 'Selected'];
$appChart = [];
foreach ($appLabels as $key => $label) {
    $appChart[$label] = $applicationFunnel[$key] ?? 0;
}

$driveRegLabels = ['pending' => 'Pending', 'shortlisted' => 'Shortlisted', 'selected' => 'Selected', 'rejected' => 'Rejected'];
$driveRegChart = [];
foreach ($driveRegLabels as $key => $label) {
    $driveRegChart[$label] = $driveRegistrationFunnel[$key] ?? 0;
}

$platformHires = ($applicationFunnel['selected'] ?? 0) + ($driveRegistrationFunnel['selected'] ?? 0);
?>
<h1 class="h4 mb-1">Platform Analytics</h1>
<p class="text-muted mb-4">Aggregate numbers across the whole platform.</p>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Institutes</div><div class="h4 mb-0"><?= (int) $instituteCount ?></div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Colleges</div><div class="h4 mb-0"><?= (int) $collegeCount ?></div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Companies</div><div class="h4 mb-0"><?= (int) $companyCount ?></div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card h-100"><div class="card-body"><div class="small text-muted mb-1">Real placements (platform-wide)</div><div class="h4 mb-0"><?= (int) $platformHires ?></div></div></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Users by role</div>
            <div class="card-body"><canvas id="roleChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Job postings by status</div>
            <div class="card-body"><canvas id="jobChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Job applications by status</div>
            <div class="card-body"><canvas id="appChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Campus drive registrations by status</div>
            <div class="card-body"><canvas id="driveRegChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="small text-muted mb-1">Revenue Analytics</div>
            <p class="small text-muted mb-0">Unlocks once payments (Phase 20) are live.</p>
        </div>
        <a href="<?= url('/dashboard/revenue-analytics') ?>" class="btn btn-sm btn-outline-secondary">View</a>
    </div>
</div>

<script src="<?= asset('js/vendor/chart.min.js') ?>"></script>
<script>
new Chart(document.getElementById('roleChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($roleChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Users', data: <?= json_encode(array_values($roleChart)) ?>, backgroundColor: '#0d6efd' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('jobChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($jobChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ data: <?= json_encode(array_values($jobChart)) ?>, backgroundColor: ['#6c757d', '#198754', '#dc3545'] }]
    }
});
new Chart(document.getElementById('appChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($appChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Applications', data: <?= json_encode(array_values($appChart)) ?>, backgroundColor: '#6f42c1' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('driveRegChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($driveRegChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Registrations', data: <?= json_encode(array_values($driveRegChart)) ?>, backgroundColor: '#fd7e14' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
