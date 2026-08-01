<?php
$categoryLabels = [
    'technical' => ['label' => 'Technical', 'icon' => 'bi-cpu'],
    'coding' => ['label' => 'Coding', 'icon' => 'bi-code-slash'],
    'english' => ['label' => 'English', 'icon' => 'bi-book'],
    'aptitude' => ['label' => 'Aptitude', 'icon' => 'bi-graph-up'],
    'communication' => ['label' => 'Communication', 'icon' => 'bi-chat-dots'],
];
?>

<h1 class="h4 mb-1">Assessments</h1>
<p class="text-muted mb-4">Practice tests to sharpen your skills and earn certificates.</p>

<div class="row g-3 mb-4">
    <?php foreach ($categories as $category): ?>
        <?php $best = $bestScores[$category] ?? null; ?>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="bi <?= e($categoryLabels[$category]['icon']) ?>"></i></div>
                    <h2 class="h6 fw-semibold mb-1"><?= e($categoryLabels[$category]['label']) ?></h2>
                    <?php if ($best !== null): ?>
                        <p class="small text-muted mb-2">Best score: <strong><?= (int) $best ?>%</strong> <?= $best >= 70 ? '<span class="badge text-bg-success">Passed</span>' : '' ?></p>
                    <?php else: ?>
                        <p class="small text-muted mb-2">Not attempted yet.</p>
                    <?php endif; ?>
                    <form method="post" action="<?= url('/dashboard/assessments/' . $category . '/start') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-primary">Start Test</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-trophy me-2 text-primary"></i>Leaderboard</div>
    <div class="card-body">
        <form method="get" action="<?= url('/dashboard/assessments') ?>" class="mb-3">
            <select name="category" class="form-select form-select-sm" style="max-width: 220px;" onchange="this.form.submit()">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category ?>" <?= $leaderboardCategory === $category ? 'selected' : '' ?>><?= e($categoryLabels[$category]['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if (empty($leaderboard)): ?>
            <p class="text-muted small mb-0">No completed attempts yet for this category.</p>
        <?php else: ?>
            <ol class="mb-0">
                <?php foreach ($leaderboard as $row): ?>
                    <li><?= e($row['full_name']) ?> &mdash; <strong><?= (int) $row['best_percent'] ?>%</strong></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clock-history me-2 text-primary"></i>Your attempt history</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>Category</th><th>Score</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($attempts)): ?>
                    <tr><td colspan="5" class="text-muted small">No attempts yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($attempts as $row): ?>
                    <tr>
                        <td><?= e($categoryLabels[$row['category']]['label'] ?? $row['category']) ?></td>
                        <td><?= $row['completed_at'] !== null ? (int) $row['percent'] . '%' : '-' ?></td>
                        <td>
                            <?php if ($row['completed_at'] === null): ?>
                                <span class="badge text-bg-secondary">In progress</span>
                            <?php elseif ($row['passed']): ?>
                                <span class="badge text-bg-success">Passed</span>
                            <?php else: ?>
                                <span class="badge text-bg-danger">Not passed</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= e($row['started_at']) ?></td>
                        <td>
                            <?php if ($row['completed_at'] === null): ?>
                                <a href="<?= url('/dashboard/assessments/attempts/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary">Continue</a>
                            <?php else: ?>
                                <a href="<?= url('/dashboard/assessments/attempts/' . $row['id'] . '/result') ?>" class="btn btn-sm btn-outline-secondary">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
