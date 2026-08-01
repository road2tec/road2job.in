<?php
// Interviews completed before the continuous-recording redesign have no
// interview_sessions.video_path (that column is new) but may still carry a
// legacy per-question video_path - fall back to showing those individually
// so nothing already-completed loses its recording.
$hasSessionVideo = !empty($interviewSession['video_path']);
$legacyVideoQuestions = $hasSessionVideo ? [] : array_filter($questions, fn ($q) => !empty($q['video_path']));
?>

<h1 class="h4 mb-1">Interview Review</h1>
<p class="text-muted mb-4">Status: <span class="badge text-bg-<?= $interviewSession['status'] === 'completed' ? 'success' : 'secondary' ?>"><?= e(ucfirst(str_replace('_', ' ', $interviewSession['status']))) ?></span></p>

<?php if ($interviewSession['status'] !== 'completed'): ?>
    <div class="alert alert-info">The candidate hasn't finished this interview yet. Check back once they've submitted it.</div>
<?php endif; ?>

<?php if ($hasSessionVideo): ?>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-camera-video me-2 text-primary"></i>Full Recording</div>
        <div class="card-body">
            <video id="interviewFullVideo" controls style="width:100%;max-width:640px;border-radius:var(--r2j-radius);" src="<?= url($interviewSession['video_path']) ?>"></video>
            <?php if (!empty($interviewSession['video_duration_seconds'])): ?>
                <p class="small text-muted mt-2 mb-0">Duration: <?= (int) floor($interviewSession['video_duration_seconds'] / 60) ?>:<?= str_pad((string) ($interviewSession['video_duration_seconds'] % 60), 2, '0', STR_PAD_LEFT) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php elseif (!empty($legacyVideoQuestions)): ?>
    <div class="alert alert-secondary small">This interview was recorded before Road2Job switched to one continuous video per interview - shown below as separate per-question clips.</div>
<?php endif; ?>

<?php foreach ($questions as $q): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><span class="badge text-bg-light border me-2"><?= e(ucfirst($q['round_type'])) ?></span><?= e($q['question_text']) ?></span>
            <?php if ($hasSessionVideo && $q['round_type'] !== 'coding' && !empty($q['answered_at']) && isset($q['answer_started_at'])): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm interview-jump-btn" data-seconds="<?= (int) $q['answer_started_at'] ?>">
                    <i class="bi bi-skip-forward me-1"></i>Jump to this answer
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($q['answered_at'])): ?>
                <p class="text-muted small mb-0">Not answered yet.</p>
            <?php elseif ($q['round_type'] === 'coding'): ?>
                <pre class="bg-light p-3 rounded small mb-0" style="white-space: pre-wrap;"><?= e($q['text_answer']) ?></pre>
            <?php elseif (!$hasSessionVideo && !empty($q['video_path'])): ?>
                <video controls src="<?= url($q['video_path']) ?>" style="width:100%;max-width:480px;"></video>
            <?php elseif ($hasSessionVideo): ?>
                <p class="text-muted small mb-0">Answered in the full recording above.</p>
            <?php else: ?>
                <p class="text-muted small mb-0">No recording available for this answer.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if ($interviewSession['status'] === 'completed'): ?>
    <div class="card">
        <div class="card-header"><i class="bi bi-clipboard-check me-2 text-primary"></i>Final Report</div>
        <div class="card-body">
            <form method="post" action="<?= url('/dashboard/interviews/' . $interviewSession['id'] . '/score') ?>">
                <?= csrf_field() ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="interview-show-keyword-score">Keyword Coverage</label>
                        <select id="interview-show-keyword-score" name="keyword_score" class="form-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= (int) ($score['keyword_score'] ?? 0) === $i ? 'selected' : '' ?>><?= $i ?>/5</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="interview-show-confidence-score">Confidence</label>
                        <select id="interview-show-confidence-score" name="confidence_score" class="form-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= (int) ($score['confidence_score'] ?? 0) === $i ? 'selected' : '' ?>><?= $i ?>/5</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="interview-show-technical-score">Technical Accuracy</label>
                        <select id="interview-show-technical-score" name="technical_score" class="form-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= (int) ($score['technical_score'] ?? 0) === $i ? 'selected' : '' ?>><?= $i ?>/5</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="interview-show-notes">Notes</label>
                    <textarea id="interview-show-notes" name="notes" class="form-control" rows="3"><?= e($score['notes'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><?= $score !== null ? 'Update report' : 'Submit report' ?></button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($hasSessionVideo): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var video = document.getElementById('interviewFullVideo');
    if (!video) return;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('.interview-jump-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            video.currentTime = parseInt(btn.dataset.seconds, 10) || 0;
            video.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
            video.play();
        });
    });
});
</script>
<?php endif; ?>
