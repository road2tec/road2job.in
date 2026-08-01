<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class MockInterviewSession extends Model
{
    protected static string $table = 'mock_interview_sessions';

    public static function create(int $studentId): string
    {
        return static::insert([
            'student_id' => $studentId,
            'status' => 'pending',
        ]);
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM mock_interview_sessions WHERE student_id = :student_id ORDER BY created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    public static function markCompleted(int $id): void
    {
        static::update($id, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Persists the one continuous session video and marks the session
     * completed in a single UPDATE, called from inside the finishSession()
     * transaction alongside the per-question timing/text-answer writes.
     */
    public static function completeWithVideo(int $id, string $videoPath, int $durationSeconds): void
    {
        static::update($id, [
            'video_path' => $videoPath,
            'video_duration_seconds' => $durationSeconds,
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
