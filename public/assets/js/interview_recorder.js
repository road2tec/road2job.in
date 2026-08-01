/**
 * Continuous-recording interview flow - vanilla JS, no build step, no
 * dependency. One MediaRecorder for the whole interview (never restarted
 * between questions), question narration via window.speechSynthesis, a
 * Web Audio API mic-level meter for the pre-interview check, and a single
 * XHR upload (for real progress events, which fetch() doesn't expose) once
 * the student finishes. Shared by both the employer-requested track
 * (interview_show.php) and the self-practice track (mock_interview_show.php)
 * - driven entirely by data-* attributes on .interview-session-recorder,
 * same convention as multi-select.js/social-links.js.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.querySelector('.interview-session-recorder');
        if (!root) {
            return;
        }

        var finishUrl = root.dataset.finishUrl;
        var csrfToken = root.dataset.csrf;
        var questions = [];

        try {
            questions = JSON.parse(root.dataset.questions || '[]');
        } catch (e) {
            questions = [];
        }

        if (questions.length === 0) {
            return;
        }

        var preScreen = root.querySelector('[data-state="pre"]');
        var activeScreen = root.querySelector('[data-state="active"]');
        var doneScreen = root.querySelector('[data-state="done"]');

        var cameraPreview = document.getElementById('cameraPreview');
        var cameraPreviewActive = document.getElementById('cameraPreviewActive');
        var cameraStatus = document.getElementById('cameraStatus');
        var micStatus = document.getElementById('micStatus');
        var micMeterFill = document.getElementById('micMeterFill');
        var startBtn = document.getElementById('startInterviewBtn');

        var questionNumberEl = document.getElementById('questionNumber');
        var questionTotalEl = document.getElementById('questionTotal');
        var questionTextEl = document.getElementById('questionText');
        var questionRoundEl = document.getElementById('questionRound');
        var questionTimeHintEl = document.getElementById('questionTimeHint');
        var progressBarEl = document.getElementById('interviewProgressBar');
        var elapsedTimerEl = document.getElementById('elapsedTimer');
        var codingAnswerWrap = document.getElementById('codingAnswerWrap');
        var codingAnswerInput = document.getElementById('codingAnswerInput');
        var nextBtn = document.getElementById('nextQuestionBtn');
        var repeatBtn = document.getElementById('repeatQuestionBtn');

        var stream = null;
        var mediaRecorder = null;
        var recordedChunks = [];
        var recordedBlob = null;
        var recordStartTime = null;
        var currentIndex = 0;
        var questionStartOffset = 0;
        var answers = [];
        var finalDurationSeconds = 0;
        var audioContext = null;
        var analyser = null;
        var micRafId = null;
        var elapsedInterval = null;

        function elapsedSeconds() {
            if (recordStartTime === null) {
                return 0;
            }
            return Math.floor((performance.now() - recordStartTime) / 1000);
        }

        function formatDuration(totalSeconds) {
            var m = Math.floor(totalSeconds / 60);
            var s = totalSeconds % 60;
            return m + ':' + (s < 10 ? '0' : '') + s;
        }

        // ---- Pre-interview: camera/mic check ----
        function initMedia() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (cameraStatus) {
                    cameraStatus.textContent = 'Camera/microphone access is not supported in this browser.';
                }
                return;
            }

            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 }, audio: true })
                .then(function (mediaStream) {
                    stream = mediaStream;
                    if (cameraPreview) {
                        cameraPreview.srcObject = stream;
                    }
                    if (cameraPreviewActive) {
                        cameraPreviewActive.srcObject = stream;
                    }
                    if (cameraStatus) {
                        cameraStatus.textContent = 'Camera ready';
                    }
                    if (micStatus) {
                        micStatus.textContent = 'Microphone ready';
                    }
                    if (startBtn) {
                        startBtn.disabled = false;
                    }
                    startMicMeter(stream);
                })
                .catch(function (err) {
                    if (cameraStatus) {
                        cameraStatus.textContent = 'Could not access camera/microphone: ' + err.message;
                    }
                    if (micStatus) {
                        micStatus.textContent = 'Grant camera and microphone permission to continue.';
                    }
                });
        }

        function startMicMeter(mediaStream) {
            if (!micMeterFill) {
                return;
            }

            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) {
                return;
            }

            try {
                audioContext = new AudioContextClass();
                var source = audioContext.createMediaStreamSource(mediaStream);
                analyser = audioContext.createAnalyser();
                analyser.fftSize = 256;
                source.connect(analyser);

                var data = new Uint8Array(analyser.frequencyBinCount);

                var poll = function () {
                    analyser.getByteFrequencyData(data);
                    var sum = 0;
                    for (var i = 0; i < data.length; i++) {
                        sum += data[i];
                    }
                    var avg = sum / data.length;
                    var pct = Math.min(100, Math.round((avg / 128) * 100));
                    micMeterFill.style.width = pct + '%';
                    micRafId = requestAnimationFrame(poll);
                };

                poll();
            } catch (e) {
                // Mic-level meter is a nice-to-have - never block the interview if unsupported.
            }
        }

        function stopMicMeter() {
            if (micRafId) {
                cancelAnimationFrame(micRafId);
            }
            if (audioContext) {
                try {
                    audioContext.close();
                } catch (e) {
                    // Already closed or unsupported - nothing to do.
                }
            }
        }

        // ---- Question narration ----
        function speakQuestion(text) {
            if (!('speechSynthesis' in window)) {
                return;
            }
            try {
                window.speechSynthesis.cancel();
                var utterance = new SpeechSynthesisUtterance(text);
                utterance.rate = 1;
                utterance.pitch = 1;
                utterance.volume = 1;
                window.speechSynthesis.speak(utterance);
            } catch (e) {
                // The question is always shown as text regardless - speech is a bonus.
            }
        }

        // ---- Question rendering ----
        function renderQuestion(index) {
            var q = questions[index];

            if (questionNumberEl) {
                questionNumberEl.textContent = index + 1;
            }
            if (questionTotalEl) {
                questionTotalEl.textContent = questions.length;
            }
            if (questionTextEl) {
                questionTextEl.textContent = q.question_text;
            }
            if (questionRoundEl) {
                questionRoundEl.textContent = q.round_type.charAt(0).toUpperCase() + q.round_type.slice(1);
            }
            if (questionTimeHintEl) {
                questionTimeHintEl.textContent = 'Suggested time: ' + Math.round(q.time_limit_seconds / 60) + ' min';
            }
            if (progressBarEl) {
                var pct = Math.round((index / questions.length) * 100);
                progressBarEl.style.width = pct + '%';
                progressBarEl.setAttribute('aria-valuenow', pct);
            }
            if (codingAnswerWrap) {
                codingAnswerWrap.hidden = q.round_type !== 'coding';
            }
            if (codingAnswerInput && q.round_type === 'coding') {
                codingAnswerInput.value = '';
                codingAnswerInput.classList.remove('is-invalid');
            }
            if (nextBtn) {
                nextBtn.textContent = index === questions.length - 1 ? 'Finish Interview' : 'Next Question';
            }

            speakQuestion(q.question_text);
        }

        // ---- Recording lifecycle ----
        function startInterview() {
            if (!stream) {
                return;
            }

            preScreen.hidden = true;
            activeScreen.hidden = false;

            var options = { mimeType: 'video/webm', videoBitsPerSecond: 500000 };
            try {
                mediaRecorder = new MediaRecorder(stream, options);
            } catch (e) {
                mediaRecorder = new MediaRecorder(stream);
            }

            mediaRecorder.ondataavailable = function (event) {
                if (event.data && event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            };
            mediaRecorder.onstop = handleRecordingStopped;

            recordedChunks = [];
            mediaRecorder.start();
            recordStartTime = performance.now();
            window.addEventListener('beforeunload', beforeUnloadHandler);

            elapsedInterval = setInterval(updateElapsedTimer, 1000);
            updateElapsedTimer();

            currentIndex = 0;
            questionStartOffset = 0;
            answers = [];
            renderQuestion(0);
        }

        function updateElapsedTimer() {
            if (elapsedTimerEl) {
                elapsedTimerEl.textContent = formatDuration(elapsedSeconds());
            }
        }

        function recordCurrentAnswer() {
            var q = questions[currentIndex];
            var endOffset = elapsedSeconds();

            var entry = {
                session_question_id: q.id,
                answer_started_at: questionStartOffset,
                answer_ended_at: endOffset,
            };

            if (q.round_type === 'coding' && codingAnswerInput) {
                entry.text_answer = codingAnswerInput.value.trim();
            }

            answers.push(entry);
            questionStartOffset = endOffset;
        }

        function goToNextQuestion() {
            var q = questions[currentIndex];

            if (q.round_type === 'coding' && codingAnswerInput && codingAnswerInput.value.trim() === '') {
                codingAnswerInput.classList.add('is-invalid');
                codingAnswerInput.focus();
                return;
            }

            recordCurrentAnswer();

            if (currentIndex === questions.length - 1) {
                finishInterview();
                return;
            }

            currentIndex++;
            renderQuestion(currentIndex);
        }

        function finishInterview() {
            clearInterval(elapsedInterval);
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
        }

        function handleRecordingStopped() {
            recordedBlob = new Blob(recordedChunks, { type: 'video/webm' });
            finalDurationSeconds = elapsedSeconds();
            stopMicMeter();

            activeScreen.hidden = true;
            doneScreen.hidden = false;

            var previewVideo = document.getElementById('finalRecordingPreview');
            if (previewVideo) {
                previewVideo.src = URL.createObjectURL(recordedBlob);
            }

            var durationEl = document.getElementById('finalDuration');
            if (durationEl) {
                durationEl.textContent = formatDuration(finalDurationSeconds);
            }

            var sizeEl = document.getElementById('finalSize');
            if (sizeEl) {
                sizeEl.textContent = (recordedBlob.size / (1024 * 1024)).toFixed(1) + ' MB';
            }

            var submitBtn = document.getElementById('submitInterviewBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }

        function beforeUnloadHandler(e) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }

        function uploadInterview() {
            if (!recordedBlob) {
                return;
            }

            var submitBtn = document.getElementById('submitInterviewBtn');
            var uploadStatus = document.getElementById('uploadStatus');
            var uploadProgressWrap = document.getElementById('uploadProgressWrap');
            var uploadProgressBar = document.getElementById('uploadProgressBar');

            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (uploadProgressWrap) {
                uploadProgressWrap.hidden = false;
            }
            if (uploadStatus) {
                uploadStatus.textContent = 'Uploading interview...';
            }

            var formData = new FormData();
            formData.append('_csrf_token', csrfToken);
            formData.append('video', recordedBlob, 'interview.webm');
            formData.append('answers', JSON.stringify(answers));
            formData.append('video_duration_seconds', finalDurationSeconds);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', finishUrl, true);

            xhr.upload.addEventListener('progress', function (event) {
                if (event.lengthComputable && uploadProgressBar) {
                    var pct = Math.round((event.loaded / event.total) * 100);
                    uploadProgressBar.style.width = pct + '%';
                    uploadProgressBar.setAttribute('aria-valuenow', pct);
                    if (uploadStatus) {
                        uploadStatus.textContent = 'Uploading interview... ' + pct + '%';
                    }
                }
            });

            xhr.onload = function () {
                var response = null;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    response = null;
                }

                if (xhr.status >= 200 && xhr.status < 300 && response && response.success) {
                    window.removeEventListener('beforeunload', beforeUnloadHandler);
                    if (uploadStatus) {
                        uploadStatus.textContent = 'Upload complete! Redirecting...';
                    }
                    window.location.href = response.redirect || window.location.href;
                    return;
                }

                var message = (response && response.message) ? response.message : 'Upload failed. Please try again.';
                if (uploadStatus) {
                    uploadStatus.textContent = message;
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            };

            xhr.onerror = function () {
                if (uploadStatus) {
                    uploadStatus.textContent = 'Upload failed - please check your connection and try again.';
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            };

            xhr.send(formData);
        }

        if (startBtn) {
            startBtn.addEventListener('click', startInterview);
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', goToNextQuestion);
        }
        if (repeatBtn) {
            repeatBtn.addEventListener('click', function () {
                speakQuestion(questions[currentIndex].question_text);
            });
        }

        var submitBtn = document.getElementById('submitInterviewBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', uploadInterview);
        }

        initMedia();
    });
})();
