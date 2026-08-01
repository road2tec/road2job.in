<?php

namespace App\Controllers;

use App\Models\InterviewQuestion;
use App\Models\MockInterviewSession;
use App\Models\MockInterviewSessionQuestion;
use App\Services\FileUploadService;
use Core\Controller;
use Core\Request;
use Core\Session;

class MockInterviewController extends Controller
{
    protected const ROUND_COUNTS = ['technical' => 2, 'hr' => 2, 'coding' => 1];

    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $this->view('dashboard/student/mock_interviews', [
            'user' => $sessionUser,
            'sessions' => MockInterviewSession::forStudent((int) $sessionUser['id']),
        ], 'student');
    }

    public function start(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $studentId = (int) $sessionUser['id'];

        $sessionId = MockInterviewSession::create($studentId);

        $questions = InterviewQuestion::randomForRounds(self::ROUND_COUNTS);
        MockInterviewSessionQuestion::createForSession((int) $sessionId, $questions);

        $this->redirect('/dashboard/mock-interviews/' . $sessionId);
    }

    public function show(Request $request, string $id): void
    {
        $session = $this->ownedSession($id);

        if ($session === null) {
            return;
        }

        $this->view('dashboard/student/mock_interview_show', [
            'user' => Session::get('_user'),
            'mockSession' => $session,
            'questions' => MockInterviewSessionQuestion::forSession((int) $id),
        ], 'student');
    }

    /**
     * Single-shot finish endpoint for the redesigned continuous-recording
     * interview flow - see InterviewController::finishSession() for the
     * full rationale (same shape, mock track has no employer link so no
     * job_application/company checks apply).
     */
    public function finishSession(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $session = MockInterviewSession::find((int) $id);

        if ($session === null || (int) $session['student_id'] !== (int) $sessionUser['id']) {
            $this->json(['success' => false, 'message' => 'That practice interview could not be found.'], 404);
            return;
        }

        if ($session['status'] === 'completed') {
            $this->json(['success' => false, 'message' => 'This practice interview has already been completed.'], 409);
            return;
        }

        $videoFile = $request->file('video');

        if ($videoFile === null || ($videoFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $this->json(['success' => false, 'message' => 'No recording was received. Please try again.'], 422);
            return;
        }

        $answers = json_decode((string) $request->input('answers', '[]'), true);

        if (!is_array($answers)) {
            $this->json(['success' => false, 'message' => 'Interview answers could not be read. Please try again.'], 422);
            return;
        }

        $allQuestions = MockInterviewSessionQuestion::forSession((int) $id);

        if (count($answers) !== count($allQuestions)) {
            $this->json(['success' => false, 'message' => 'Please answer every question before finishing.'], 422);
            return;
        }

        $durationSeconds = max(0, (int) $request->input('video_duration_seconds', 0));
        $validated = [];

        foreach ($answers as $answer) {
            if (!is_array($answer) || !isset($answer['session_question_id'])) {
                $this->json(['success' => false, 'message' => 'Interview answers were malformed. Please try again.'], 422);
                return;
            }

            $question = MockInterviewSessionQuestion::findForSession((int) $id, (int) $answer['session_question_id']);

            if ($question === null) {
                $this->json(['success' => false, 'message' => 'One of the interview questions could not be verified.'], 422);
                return;
            }

            $startedAt = min($durationSeconds ?: PHP_INT_MAX, max(0, (int) ($answer['answer_started_at'] ?? 0)));
            $endedAt = min($durationSeconds ?: PHP_INT_MAX, max($startedAt, (int) ($answer['answer_ended_at'] ?? $startedAt)));

            $textAnswer = null;

            if ($question['round_type'] === 'coding') {
                $textAnswer = trim((string) ($answer['text_answer'] ?? ''));

                if ($textAnswer === '') {
                    $this->json(['success' => false, 'message' => 'Please write an answer for every coding question before finishing.'], 422);
                    return;
                }
            }

            $validated[] = [
                'id' => (int) $question['id'],
                'text_answer' => $textAnswer,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
            ];
        }

        try {
            $videoPath = (new FileUploadService())->upload($videoFile, 'video');
        } catch (\RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            return;
        }

        $db = \Core\Database::connection();
        $db->beginTransaction();

        try {
            foreach ($validated as $entry) {
                MockInterviewSessionQuestion::saveAnswer($entry['id'], $entry['text_answer'], $entry['started_at'], $entry['ended_at']);
            }

            MockInterviewSession::completeWithVideo((int) $id, $videoPath, $durationSeconds);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Something went wrong saving your practice interview. Please try again.'], 500);
            return;
        }

        $this->json(['success' => true, 'redirect' => url('/dashboard/mock-interviews/' . $id)]);
    }

    protected function ownedSession(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $session = MockInterviewSession::find((int) $id);

        if ($session === null || (int) $session['student_id'] !== (int) $sessionUser['id']) {
            Session::flash('error', 'That practice interview could not be found.');
            $this->redirect('/dashboard/mock-interviews');
            return null;
        }

        return $session;
    }
}
