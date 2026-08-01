<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class AssessmentAttemptAnswer extends Model
{
    protected static string $table = 'assessment_attempt_answers';

    public static function createForAttempt(int $attemptId, array $questions): void
    {
        $order = 0;

        foreach ($questions as $question) {
            static::insert([
                'assessment_attempt_id' => $attemptId,
                'assessment_question_id' => (int) $question['id'],
                'order_index' => $order++,
            ]);
        }
    }

    public static function forAttempt(int $attemptId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT aa.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option
             FROM assessment_attempt_answers aa
             JOIN assessment_questions q ON q.id = aa.assessment_question_id
             WHERE aa.assessment_attempt_id = :attempt_id
             ORDER BY aa.order_index ASC"
        );
        $stmt->execute(['attempt_id' => $attemptId]);

        return $stmt->fetchAll();
    }

    public static function saveAnswer(int $id, string $selectedOption): void
    {
        static::update($id, ['selected_option' => $selectedOption]);
    }

    public static function gradeAttempt(int $attemptId): array
    {
        $rows = self::forAttempt($attemptId);
        $score = 0;

        foreach ($rows as $row) {
            $isCorrect = $row['selected_option'] !== null && $row['selected_option'] === $row['correct_option'];

            if ($isCorrect) {
                $score++;
            }

            static::update((int) $row['id'], ['is_correct' => $isCorrect ? 1 : 0]);
        }

        return ['score' => $score, 'total' => count($rows)];
    }
}
