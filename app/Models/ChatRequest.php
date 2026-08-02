<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class ChatRequest extends Model
{
    protected static string $table = 'chat_requests';

    public static function create(int $employerUserId, int $studentId, ?int $jobApplicationId, ?string $message): string
    {
        return static::insert([
            'employer_user_id' => $employerUserId,
            'student_id' => $studentId,
            'job_application_id' => $jobApplicationId,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    public static function hasActiveRequest(int $employerUserId, int $studentId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM chat_requests WHERE employer_user_id = :employer_user_id AND student_id = :student_id AND status = 'pending' LIMIT 1"
        );
        $stmt->execute(['employer_user_id' => $employerUserId, 'student_id' => $studentId]);

        return $stmt->fetchColumn() !== false;
    }

    public static function forEmployer(int $employerUserId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS student_name
             FROM chat_requests r
             JOIN users u ON u.id = r.student_id
             WHERE r.employer_user_id = :employer_user_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['employer_user_id' => $employerUserId]);

        return $stmt->fetchAll();
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS employer_name, c.name AS company_name
             FROM chat_requests r
             JOIN users u ON u.id = r.employer_user_id
             LEFT JOIN companies c ON c.user_id = r.employer_user_id
             WHERE r.student_id = :student_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }
}
