<?php
$isCompleted = $mockSession['status'] === 'completed';
$questionsForJs = array_map(fn ($q) => [
    'id' => (int) $q['id'],
    'round_type' => $q['round_type'],
    'question_text' => $q['question_text'],
    'time_limit_seconds' => (int) $q['time_limit_seconds'],
], $questions);
?>

<h1 class="h4 mb-1">Mock Interview</h1>
<p class="text-muted mb-4">Status: <span class="badge text-bg-<?= $isCompleted ? 'success' : 'secondary' ?>"><?= e(ucfirst(str_replace('_', ' ', $mockSession['status']))) ?></span></p>

<?php if ($isCompleted): ?>
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-camera-video me-2 text-primary"></i>Your Recording</div>
        <div class="card-body">
            <?php if (!empty($mockSession['video_path'])): ?>
                <video controls style="width:100%;max-width:640px;border-radius:var(--r2j-radius);" src="<?= url($mockSession['video_path']) ?>"></video>
            <?php else: ?>
                <p class="text-muted small mb-0">No recording is available for this practice interview.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Questions</div>
        <div class="list-group list-group-flush">
            <?php foreach ($questions as $i => $q): ?>
                <div class="list-group-item">
                    <span class="badge text-bg-light border me-2"><?= e(ucfirst($q['round_type'])) ?></span><?= $i + 1 ?>. <?= e($q['question_text']) ?>
                    <?php if ($q['round_type'] === 'coding' && !empty($q['text_answer'])): ?>
                        <pre class="small bg-light p-2 rounded mt-2 mb-0"><?= e($q['text_answer']) ?></pre>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="mb-2">You've completed this practice interview. Review your recording above, or get feedback from a mentor.</p>
            <a href="<?= url('/mentors?service=mock-interview-feedback&mock_session=' . $mockSession['id']) ?>" class="btn btn-outline-primary btn-sm">Request mentor feedback</a>
        </div>
    </div>
<?php else: ?>
    <div class="interview-session-recorder"
         data-finish-url="<?= url('/dashboard/mock-interviews/' . $mockSession['id'] . '/finish') ?>"
         data-csrf="<?= csrf_token() ?>"
         data-questions='<?= json_encode($questionsForJs, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>

        <div data-state="pre">
            <div class="card">
                <div class="card-header"><i class="bi bi-camera-video me-2 text-primary"></i>Get Ready</div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="interview-camera-frame">
                                <video id="cameraPreview" autoplay muted playsinline></video>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled small mb-3">
                                <li class="mb-2"><i class="bi bi-camera me-2 text-primary"></i><span id="cameraStatus">Requesting camera access...</span></li>
                                <li class="mb-1"><i class="bi bi-mic me-2 text-primary"></i><span id="micStatus">Checking microphone...</span></li>
                            </ul>
                            <div class="interview-mic-meter mb-4"><div id="micMeterFill" class="interview-mic-meter__fill"></div></div>

                            <p class="small fw-semibold mb-1">Before you start:</p>
                            <ul class="small text-muted mb-4">
                                <li>Find a quiet location with good lighting.</li>
                                <li>Keep your face clearly visible to the camera.</li>
                                <li>Speak naturally - there's no need to rush.</li>
                                <li>Recording continues through all <?= count($questions) ?> questions until you finish.</li>
                            </ul>

                            <button type="button" id="startInterviewBtn" class="btn btn-primary" disabled>
                                <i class="bi bi-play-fill me-1"></i>Start Interview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div data-state="active" hidden>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <span class="d-inline-flex align-items-center gap-2 small fw-semibold text-danger">
                            <span class="interview-rec-dot"></span>Recording
                        </span>
                        <span class="small text-muted">Question <span id="questionNumber">1</span> of <span id="questionTotal"><?= count($questions) ?></span></span>
                        <span class="small text-muted font-monospace" id="elapsedTimer">0:00</span>
                    </div>
                    <div class="progress mb-4" style="height:.4rem;">
                        <div id="interviewProgressBar" class="progress-bar" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="interview-camera-frame">
                                <video id="cameraPreviewActive" autoplay muted playsinline></video>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="badge text-bg-light border mb-2" id="questionRound"></span>
                            <p class="fs-5 mb-2" id="questionText"></p>
                            <p class="small text-muted mb-3" id="questionTimeHint"></p>
                            <button type="button" id="repeatQuestionBtn" class="btn btn-outline-secondary btn-sm mb-3">
                                <i class="bi bi-volume-up me-1"></i>Repeat Question
                            </button>

                            <div id="codingAnswerWrap" hidden>
                                <label class="form-label" for="codingAnswerInput">Your answer</label>
                                <textarea id="codingAnswerInput" class="form-control font-monospace" rows="8" placeholder="Write your answer or pseudocode here..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end">
                    <button type="button" id="nextQuestionBtn" class="btn btn-primary">Next Question</button>
                </div>
            </div>
        </div>

        <div data-state="done" hidden>
            <div class="card">
                <div class="card-header"><i class="bi bi-check-circle me-2 text-success"></i>Practice Interview Completed</div>
                <div class="card-body">
                    <div class="row g-4 align-items-start">
                        <div class="col-md-6">
                            <video id="finalRecordingPreview" controls style="width:100%;max-width:400px;border-radius:var(--r2j-radius);"></video>
                        </div>
                        <div class="col-md-6">
                            <p class="small mb-1">Duration: <strong id="finalDuration">0:00</strong></p>
                            <p class="small mb-3">File size: <strong id="finalSize">-</strong></p>
                            <button type="button" id="submitInterviewBtn" class="btn btn-primary" disabled>
                                <i class="bi bi-upload me-1"></i>Submit Interview
                            </button>
                            <div id="uploadProgressWrap" class="mt-3" hidden>
                                <div class="progress mb-1" style="height:.4rem;">
                                    <div id="uploadProgressBar" class="progress-bar" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <p class="small text-muted mb-0" id="uploadStatus"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= asset('js/interview_recorder.js') ?>"></script>
<?php endif; ?>
