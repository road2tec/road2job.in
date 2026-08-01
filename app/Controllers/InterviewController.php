<?php

namespace App\Controllers;

use App\Models\Company;
use App\Models\InterviewQuestion;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\InterviewSessionQuestion;
use App\Models\JobApplication;
use App\Services\FileUploadService;
use Core\Controller;
use Core\Request;
use Core\Session;

class InterviewController extends Controller
{
    protected const ROUND_COUNTS = ['technical' => 2, 'hr' => 2, 'coding' => 1];

    // ---- Employer side ----

    public function request(Request $request, string $applicationId): void
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);
        $application = JobApplication::find((int) $applicationId);

        if ($application === null || $company === null || (int) $application['company_id'] !== (int) $company['id']) {
            Session::flash('error', 'That application could not be found.');
            $this->redirect('/dashboard/applicants');
            return;
        }

        if (InterviewSession::findByApplication((int) $applicationId) !== null) {
            Session::flash('error', 'An interview has already been requested for this applicant.');
            $this->redirect('/dashboard/applicants');
            return;
        }

        $sessionId = InterviewSession::create((int) $applicationId, (int) $company['id'], (int) $application['student_id']);

        $questions = InterviewQuestion::randomForRounds(self::ROUND_COUNTS);
        InterviewSessionQuestion::createForSession((int) $sessionId, $questions);

        Session::flash('success', 'Interview requested. The candidate will be notified to complete it.');
        $this->redirect('/dashboard/applicants');
    }

    public function manageIndex(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);

        $this->view('dashboard/employer/interviews', [
            'user' => $sessionUser,
            'sessions' => $company !== null ? InterviewSession::forCompany((int) $company['id']) : [],
        ], 'employer');
    }

    public function showForEmployer(Request $request, string $id): void
    {
        $session = $this->ownedSessionForEmployer($id);

        if ($session === null) {
            return;
        }

        $this->view('dashboard/employer/interview_show', [
            'user' => Session::get('_user'),
            'interviewSession' => $session,
            'questions' => InterviewSessionQuestion::forSession((int) $id),
            'score' => InterviewScore::findBySession((int) $id),
        ], 'employer');
    }

    public function submitScore(Request $request, string $id): void
    {
        $session = $this->ownedSessionForEmployer($id);

        if ($session === null) {
            return;
        }

        $sessionUser = Session::get('_user');

        $keyword = (int) $request->input('keyword_score', 0);
        $confidence = (int) $request->input('confidence_score', 0);
        $technical = (int) $request->input('technical_score', 0);

        foreach ([$keyword, $confidence, $technical] as $score) {
            if ($score < 1 || $score > 5) {
                Session::flash('error', 'Please rate every category from 1 to 5.');
                $this->redirect('/dashboard/interviews/' . $id);
                return;
            }
        }

        InterviewScore::saveForSession((int) $id, (int) $sessionUser['id'], [
            'keyword_score' => $keyword,
            'confidence_score' => $confidence,
            'technical_score' => $technical,
            'notes' => trim((string) $request->input('notes', '')) ?: null,
        ]);

        Session::flash('success', 'Interview report saved.');
        $this->redirect('/dashboard/interviews/' . $id);
    }

    protected function ownedSessionForEmployer(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);
        $session = InterviewSession::find((int) $id);

        if ($session === null || $company === null || (int) $session['company_id'] !== (int) $company['id']) {
            Session::flash('error', 'That interview could not be found.');
            $this->redirect('/dashboard/interviews');
            return null;
        }

        return $session;
    }

    // ---- Student side ----

    public function studentIndex(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $this->view('dashboard/student/interviews', [
            'user' => $sessionUser,
            'sessions' => InterviewSession::forStudent((int) $sessionUser['id']),
        ], 'student');
    }

    public function studentShow(Request $request, string $id): void
    {
        $session = $this->ownedSessionForStudent($id);

        if ($session === null) {
            return;
        }

        $this->view('dashboard/student/interview_show', [
            'user' => Session::get('_user'),
            'interviewSession' => $session,
            'questions' => InterviewSessionQuestion::forSession((int) $id),
            'score' => $session['status'] === 'completed' ? InterviewScore::findBySession((int) $id) : null,
        ], 'student');
    }

    /**
     * Single-shot finish endpoint for the redesigned continuous-recording
     * interview flow (one MediaRecorder for the whole session, not one per
     * question) - replaces the old per-question submitAnswer() entirely, no
     * in-flight sessions existed under the old flow at the time this shipped
     * so no compatibility branch was needed. Called via XHR (not a plain
     * form submit) so it responds with JSON, not a flash+redirect - the
     * client needs to know upload success/failure without losing the
     * already-recorded video blob on error.
     */
    public function finishSession(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $session = InterviewSession::find((int) $id);

        if ($session === null || (int) $session['student_id'] !== (int) $sessionUser['id']) {
            $this->json(['success' => false, 'message' => 'That interview could not be found.'], 404);
            return;
        }

        if ($session['status'] === 'completed') {
            $this->json(['success' => false, 'message' => 'This interview has already been completed.'], 409);
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

        $allQuestions = InterviewSessionQuestion::forSession((int) $id);

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

            $question = InterviewSessionQuestion::findForSession((int) $id, (int) $answer['session_question_id']);

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
                InterviewSessionQuestion::saveAnswer($entry['id'], $entry['text_answer'], $entry['started_at'], $entry['ended_at']);
            }

            InterviewSession::completeWithVideo((int) $id, $videoPath, $durationSeconds);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Something went wrong saving your interview. Please try again.'], 500);
            return;
        }

        $this->json(['success' => true, 'redirect' => url('/dashboard/my-interviews/' . $id)]);
    }

    protected function ownedSessionForStudent(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $session = InterviewSession::find((int) $id);

        if ($session === null || (int) $session['student_id'] !== (int) $sessionUser['id']) {
            Session::flash('error', 'That interview could not be found.');
            $this->redirect('/dashboard/my-interviews');
            return null;
        }

        return $session;
    }
}
