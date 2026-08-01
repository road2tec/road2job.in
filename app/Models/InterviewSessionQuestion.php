<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InterviewSessionQuestion extends Model
{
    protected static string $table = 'interview_session_questions';

    public static function createForSession(int $sessionId, array $questions): void
    {
        $order = 0;

        foreach ($questions as $question) {
            static::insert([
                'interview_session_id' => $sessionId,
                'interview_question_id' => (int) $question['id'],
                'order_index' => $order++,
            ]);
        }
    }

    public static function forSession(int $sessionId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT sq.*, q.round_type, q.question_text, q.time_limit_seconds
             FROM interview_session_questions sq
             JOIN interview_questions q ON q.id = sq.interview_question_id
             WHERE sq.interview_session_id = :session_id
             ORDER BY sq.order_index ASC"
        );
        $stmt->execute(['session_id' => $sessionId]);

        return $stmt->fetchAll();
    }

    public static function findForSession(int $sessionId, int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT sq.*, q.round_type, q.question_text, q.time_limit_seconds
             FROM interview_session_questions sq
             JOIN interview_questions q ON q.id = sq.interview_question_id
             WHERE sq.id = :id AND sq.interview_session_id = :session_id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'session_id' => $sessionId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function allAnswered(int $sessionId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM interview_session_questions WHERE interview_session_id = :session_id AND answered_at IS NULL"
        );
        $stmt->execute(['session_id' => $sessionId]);

        return (int) $stmt->fetchColumn() === 0;
    }

    /**
     * Records one question's slice of the single continuous session
     * recording - timing offsets always, text_answer only for coding-round
     * questions (video-round questions are answered by the shared session
     * video itself, not a per-question upload). Caller (finishSession())
     * must already have validated this row belongs to the session via
     * findForSession() before calling this.
     */
    public static function saveAnswer(int $id, ?string $textAnswer, int $startedAt, int $endedAt): void
    {
        $data = [
            'answer_started_at' => $startedAt,
            'answer_ended_at' => $endedAt,
            'answered_at' => date('Y-m-d H:i:s'),
        ];

        if ($textAnswer !== null) {
            $data['text_answer'] = $textAnswer;
        }

        static::update($id, $data);
    }
}
