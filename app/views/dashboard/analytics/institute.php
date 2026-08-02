<?php
$courseLabels = ['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'];
$courseChart = [];
foreach ($courseLabels as $key => $label) {
    $courseChart[$label] = $courseFunnel[$key] ?? 0;
}
?>
<h1 class="h4 mb-1">Analytics</h1>
<p class="text-muted mb-4">Your course listings by status.</p>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Courses by status</div>
            <div class="card-body"><canvas id="courseChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<script src="<?= asset('js/vendor/chart.min.js') ?>"></script>
<script>
new Chart(document.getElementById('courseChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($courseChart), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{ data: <?= json_encode(array_values($courseChart)) ?>, backgroundColor: ['#6c757d', '#198754', '#dc3545'] }]
    }
});
</script>
