<?php
$funnelLabels = ['applied' => 'Applied', 'under_review' => 'Under Review', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'selected' => 'Selected'];
$funnelChart = [];
foreach ($funnelLabels as $key => $label) {
    $funnelChart[$label] = $applicationFunnel[$key] ?? 0;
}

$assessmentChart = [];
foreach ($assessmentScores as $category => $percent) {
    $assessmentChart[ucfirst($category)] = $percent;
}
?>
<h1 class="h4 mb-1">Analytics</h1>
<p class="text-muted mb-4">Your application funnel and assessment performance.</p>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Application funnel</div>
            <div class="card-body"><canvas id="applicationFunnelChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Assessment best scores by category</div>
            <div class="card-body">
                <?php if (empty($assessmentChart)): ?>
                    <p class="text-muted small mb-0">No completed assessments yet.</p>
                <?php else: ?>
                    <canvas id="assessmentChart" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/vendor/chart.min.js') ?>"></script>
<script>
new Chart(document.getElementById('applicationFunnelChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($funnelChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Applications', data: <?= json_encode(array_values($funnelChart)) ?>, backgroundColor: '#0d6efd' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
<?php if (!empty($assessmentChart)): ?>
new Chart(document.getElementById('assessmentChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($assessmentChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Best %', data: <?= json_encode(array_values($assessmentChart)) ?>, backgroundColor: '#198754' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
});
<?php endif; ?>
</script>
