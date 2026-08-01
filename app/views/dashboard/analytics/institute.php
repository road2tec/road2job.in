<?php
$enrollmentLabels = ['pending' => 'Pending', 'contacted' => 'Contacted', 'enrolled' => 'Enrolled', 'completed' => 'Completed', 'declined' => 'Declined'];
$enrollmentChart = [];
foreach ($enrollmentLabels as $key => $label) {
    $enrollmentChart[$label] = $enrollmentFunnel[$key] ?? 0;
}

$courseLabels = ['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'];
$courseChart = [];
foreach ($courseLabels as $key => $label) {
    $courseChart[$label] = $courseFunnel[$key] ?? 0;
}
?>
<h1 class="h4 mb-1">Analytics</h1>
<p class="text-muted mb-4">Enrollment pipeline and course status.</p>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Enrollment funnel</div>
            <div class="card-body"><canvas id="enrollmentChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Courses by status</div>
            <div class="card-body"><canvas id="courseChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<script src="<?= asset('js/vendor/chart.min.js') ?>"></script>
<script>
new Chart(document.getElementById('enrollmentChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($enrollmentChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ label: 'Requests', data: <?= json_encode(array_values($enrollmentChart)) ?>, backgroundColor: '#0d6efd' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('courseChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($courseChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ data: <?= json_encode(array_values($courseChart)) ?>, backgroundColor: ['#6c757d', '#198754', '#dc3545'] }]
    }
});
</script>
