<?php

namespace App\Controllers;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Models\AssessmentQuestion;
use Core\Controller;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;

class AssessmentController extends Controller
{
    protected const CATEGORIES = ['technical', 'coding', 'english', 'aptitude', 'communication'];
    protected const QUESTIONS_PER_ATTEMPT = 5;

    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $studentId = (int) $sessionUser['id'];

        $leaderboardCategory = (string) $request->input('category', 'technical');
        if (!in_array($leaderboardCategory, self::CATEGORIES, true)) {
            $leaderboardCategory = 'technical';
        }

        $this->view('dashboard/student/assessments/index', [
            'user' => $sessionUser,
            'categories' => self::CATEGORIES,
            'bestScores' => AssessmentAttempt::bestPerCategory($studentId),
            'attempts' => AssessmentAttempt::forStudent($studentId),
            'leaderboardCategory' => $leaderboardCategory,
            'leaderboard' => AssessmentAttempt::leaderboard($leaderboardCategory),
        ], 'student');
    }

    public function start(Request $request, string $category): void
    {
        $sessionUser = Session::get('_user');

        if (!in_array($category, self::CATEGORIES, true)) {
            Session::flash('error', 'Invalid category.');
            $this->redirect('/dashboard/assessments');
            return;
        }

        $questions = AssessmentQuestion::randomForCategory($category, self::QUESTIONS_PER_ATTEMPT);

        if (count($questions) < self::QUESTIONS_PER_ATTEMPT) {
            Session::flash('error', 'Not enough questions available for this category yet.');
            $this->redirect('/dashboard/assessments');
            return;
        }

        $attemptId = AssessmentAttempt::create((int) $sessionUser['id'], $category, count($questions));
        AssessmentAttemptAnswer::createForAttempt((int) $attemptId, $questions);

        $this->redirect('/dashboard/assessments/attempts/' . $attemptId);
    }

    public function show(Request $request, string $id): void
    {
        $attempt = $this->ownedAttempt($id);

        if ($attempt === null) {
            return;
        }

        if ($attempt['completed_at'] !== null) {
            $this->redirect('/dashboard/assessments/attempts/' . $id . '/result');
            return;
        }

        $this->view('dashboard/student/assessments/take', [
            'user' => Session::get('_user'),
            'attempt' => $attempt,
            'answers' => AssessmentAttemptAnswer::forAttempt((int) $id),
        ], 'student');
    }

    public function submit(Request $request, string $id): void
    {
        $attempt = $this->ownedAttempt($id);

        if ($attempt === null) {
            return;
        }

        if ($attempt['completed_at'] !== null) {
            $this->redirect('/dashboard/assessments/attempts/' . $id . '/result');
            return;
        }

        $answers = AssessmentAttemptAnswer::forAttempt((int) $id);

        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            foreach ($answers as $answer) {
                $selected = $request->input('answer_' . $answer['id']);

                if (in_array($selected, ['a', 'b', 'c', 'd'], true)) {
                    AssessmentAttemptAnswer::saveAnswer((int) $answer['id'], $selected);
                }
            }

            $result = AssessmentAttemptAnswer::gradeAttempt((int) $id);
            AssessmentAttempt::markCompleted((int) $id, $result['score'], $result['total']);

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }

        $this->redirect('/dashboard/assessments/attempts/' . $id . '/result');
    }

    public function result(Request $request, string $id): void
    {
        $attempt = $this->ownedAttempt($id);

        if ($attempt === null) {
            return;
        }

        if ($attempt['completed_at'] === null) {
            $this->redirect('/dashboard/assessments/attempts/' . $id);
            return;
        }

        $this->view('dashboard/student/assessments/result', [
            'user' => Session::get('_user'),
            'attempt' => $attempt,
            'answers' => AssessmentAttemptAnswer::forAttempt((int) $id),
        ], 'student');
    }

    public function certificate(Request $request, string $id): void
    {
        $attempt = $this->ownedAttempt($id);

        if ($attempt === null) {
            return;
        }

        if (empty($attempt['passed'])) {
            Response::abort(404);
            return;
        }

        $this->view('dashboard/student/assessments/certificate', [
            'user' => Session::get('_user'),
            'attempt' => $attempt,
        ], 'certificate');
    }

    protected function ownedAttempt(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $attempt = AssessmentAttempt::find((int) $id);

        if ($attempt === null || (int) $attempt['student_id'] !== (int) $sessionUser['id']) {
            Session::flash('error', 'That assessment attempt could not be found.');
            $this->redirect('/dashboard/assessments');
            return null;
        }

        return $attempt;
    }
}
