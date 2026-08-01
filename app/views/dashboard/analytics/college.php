<?php
$registrationLabels = ['pending' => 'Pending', 'shortlisted' => 'Shortlisted', 'selected' => 'Selected', 'rejected' => 'Rejected'];
$registrationChart = [];
foreach ($registrationLabels as $key => $label) {
    $registrationChart[$label] = $registrationFunnel[$key] ?? 0;
}

$driveLabels = ['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'];
$driveChart = [];
foreach ($driveLabels as $key => $label) {
    $driveChart[$label] = $driveFunnel[$key] ?? 0;
}
?>
<h1 class="h4 mb-1">Analytics</h1>
<p class="text-muted mb-4">Campus drive registrations and real placements.</p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Real placements</div>
                <div class="h3 mb-0"><?= (int) $hireCount ?></div>
                <p class="small text-muted mb-0 mt-1">Registrations marked "selected"</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">Campus drives by status</div>
            <div class="card-body"><canvas id="driveChart" height="140"></canvas></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Registration funnel</div>
    <div class="card-body"><canvas id="registrationChart" height="220"></canvas></div>
</div>

<script src="<?= asset('js/vendor/chart.min.js') ?>"></script>
<script>
new Chart(document.getElementById('driveChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($driveChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Drives', data: <?= json_encode(array_values($driveChart)) ?>, backgroundColor: '#0d6efd' }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('registrationChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($registrationChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Registrations', data: <?= json_encode(array_values($registrationChart)) ?>, backgroundColor: '#198754' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
