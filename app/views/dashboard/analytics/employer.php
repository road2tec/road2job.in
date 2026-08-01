<?php
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

$interviewLabels = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'];
$interviewChart = [];
foreach ($interviewLabels as $key => $label) {
    $interviewChart[$label] = $interviewFunnel[$key] ?? 0;
}
?>
<h1 class="h4 mb-1">Analytics</h1>
<p class="text-muted mb-4">Job postings, applicant pipeline, and interview activity.</p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Real hires</div>
                <div class="h3 mb-0"><?= (int) $hireCount ?></div>
                <p class="small text-muted mb-0 mt-1">Applications marked "selected"</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">Job postings by status</div>
            <div class="card-body"><canvas id="jobChart" height="140"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Applicant funnel</div>
            <div class="card-body"><canvas id="appChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Interview sessions</div>
            <div class="card-body"><canvas id="interviewChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<script src="<?= asset('js/vendor/chart.min.js') ?>"></script>
<script>
new Chart(document.getElementById('jobChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($jobChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Jobs', data: <?= json_encode(array_values($jobChart)) ?>, backgroundColor: '#0d6efd' }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('appChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($appChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Applications', data: <?= json_encode(array_values($appChart)) ?>, backgroundColor: '#6f42c1' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('interviewChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($interviewChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ data: <?= json_encode(array_values($interviewChart)) ?>, backgroundColor: ['#6c757d', '#0dcaf0', '#198754'] }]
    }
});
</script>
