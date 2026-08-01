<?php

namespace App\Models;

use Core\Model;

class InterviewScore extends Model
{
    protected static string $table = 'interview_scores';

    public static function findBySession(int $sessionId): ?array
    {
        return static::findBy('interview_session_id', $sessionId);
    }

    public static function saveForSession(int $sessionId, int $scoredBy, array $data): void
    {
        $existing = static::findBySession($sessionId);

        $data['interview_session_id'] = $sessionId;
        $data['scored_by'] = $scoredBy;

        if ($existing === null) {
            static::insert($data);
            return;
        }

        static::update((int) $existing['id'], $data);
    }
}
