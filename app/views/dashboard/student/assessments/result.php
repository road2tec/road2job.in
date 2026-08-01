<h1 class="h4 mb-1"><?= e(ucfirst($attempt['category'])) ?> Assessment Result</h1>
<p class="text-muted mb-4">Completed <?= e($attempt['completed_at']) ?></p>

<div class="card mb-4">
    <div class="card-body text-center">
        <div class="display-6 fw-bold <?= $attempt['passed'] ? 'text-success' : 'text-danger' ?>"><?= (int) $attempt['percent'] ?>%</div>
        <p class="mb-2"><?= (int) $attempt['score'] ?> out of <?= (int) $attempt['total_questions'] ?> correct</p>
        <?php if ($attempt['passed']): ?>
            <span class="badge text-bg-success p-2 mb-2">Passed</span>
            <div><a href="<?= url('/dashboard/assessments/attempts/' . $attempt['id'] . '/certificate') ?>" class="btn btn-primary btn-sm mt-2" target="_blank">View Certificate</a></div>
        <?php else: ?>
            <span class="badge text-bg-secondary p-2 mb-2">Not passed (70% required)</span>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($answers as $i => $answer): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Question <?= $i + 1 ?></span>
            <?php if ($answer['is_correct']): ?>
                <span class="badge text-bg-success"><i class="bi bi-check-lg"></i> Correct</span>
            <?php else: ?>
                <span class="badge text-bg-danger"><i class="bi bi-x-lg"></i> Incorrect</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <p class="mb-2"><?= e($answer['question_text']) ?></p>
            <?php foreach (['a', 'b', 'c', 'd'] as $option): ?>
                <?php
                $classes = '';
                if ($option === $answer['correct_option']) {
                    $classes = 'text-success fw-semibold';
                } elseif ($option === $answer['selected_option']) {
                    $classes = 'text-danger';
                }
                ?>
                <p class="mb-1 small <?= $classes ?>">
                    <?= strtoupper($option) ?>. <?= e($answer['option_' . $option]) ?>
                    <?= $option === $answer['correct_option'] ? ' <i class="bi bi-check-lg"></i>' : '' ?>
                    <?= $option === $answer['selected_option'] && $option !== $answer['correct_option'] ? ' (your answer)' : '' ?>
                </p>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<a href="<?= url('/dashboard/assessments') ?>" class="btn btn-outline-secondary">&larr; Back to assessments</a>
