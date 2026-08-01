<h1 class="h4 mb-1"><?= e(ucfirst($attempt['category'])) ?> Assessment</h1>
<p class="text-muted mb-4">Answer all questions, then submit. Time remaining: <strong id="assessmentTimer">10:00</strong></p>

<form method="post" action="<?= url('/dashboard/assessments/attempts/' . $attempt['id'] . '/submit') ?>" id="assessmentForm" data-guard-submit>
    <?= csrf_field() ?>

    <?php foreach ($answers as $i => $answer): ?>
        <div class="card mb-3">
            <div class="card-header">Question <?= $i + 1 ?> of <?= count($answers) ?></div>
            <div class="card-body">
                <p class="mb-3"><?= e($answer['question_text']) ?></p>
                <?php foreach (['a', 'b', 'c', 'd'] as $option): ?>
                    <div class="form-check mb-2">
                        <input type="radio" name="answer_<?= $answer['id'] ?>" value="<?= $option ?>" class="form-check-input" id="q<?= $answer['id'] ?>_<?= $option ?>" required>
                        <label class="form-check-label" for="q<?= $answer['id'] ?>_<?= $option ?>"><?= e($answer['option_' . $option]) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary">Submit Assessment</button>
</form>

<script>
(function () {
    var secondsLeft = 10 * 60;
    var timerEl = document.getElementById('assessmentTimer');
    var form = document.getElementById('assessmentForm');

    var interval = setInterval(function () {
        secondsLeft--;
        var minutes = Math.floor(secondsLeft / 60);
        var seconds = secondsLeft % 60;
        timerEl.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;

        if (secondsLeft <= 0) {
            clearInterval(interval);
            form.submit();
        }
    }, 1000);
})();
</script>
