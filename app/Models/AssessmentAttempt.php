<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class AssessmentAttempt extends Model
{
    protected static string $table = 'assessment_attempts';

    public static function create(int $studentId, string $category, int $totalQuestions): string
    {
        return static::insert([
            'student_id' => $studentId,
            'category' => $category,
            'total_questions' => $totalQuestions,
            'started_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM assessment_attempts WHERE student_id = :student_id ORDER BY created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    public static function bestPerCategory(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT category, MAX(percent) AS best_percent
             FROM assessment_attempts
             WHERE student_id = :student_id AND completed_at IS NOT NULL
             GROUP BY category"
        );
        $stmt->execute(['student_id' => $studentId]);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['category']] = (int) $row['best_percent'];
        }

        return $result;
    }

    public static function leaderboard(string $category, int $limit = 20): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT u.full_name, best.best_percent
             FROM (
                 SELECT student_id, MAX(percent) AS best_percent
                 FROM assessment_attempts
                 WHERE category = :category AND completed_at IS NOT NULL
                 GROUP BY student_id
             ) best
             JOIN users u ON u.id = best.student_id
             ORDER BY best.best_percent DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute(['category' => $category]);

        return $stmt->fetchAll();
    }

    public static function markCompleted(int $id, int $score, int $total): void
    {
        $percent = (int) round(($score / $total) * 100);

        static::update($id, [
            'score' => $score,
            'percent' => $percent,
            'passed' => $percent >= 70 ? 1 : 0,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
