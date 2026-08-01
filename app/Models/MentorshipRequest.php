<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class MentorshipRequest extends Model
{
    protected static string $table = 'mentorship_requests';

    public static function create(
        int $mentorProfileId,
        int $studentId,
        ?string $message,
        string $serviceType = 'mentorship',
        ?string $resumeSnapshot = null,
        ?int $mockInterviewSessionId = null
    ): string {
        return static::insert([
            'mentor_profile_id' => $mentorProfileId,
            'student_id' => $studentId,
            'service_type' => $serviceType,
            'message' => $message,
            'resume_snapshot' => $resumeSnapshot,
            'mock_interview_session_id' => $mockInterviewSessionId,
            'status' => 'pending',
        ]);
    }

    public static function hasActiveRequest(int $mentorProfileId, int $studentId, string $serviceType = 'mentorship'): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM mentorship_requests
             WHERE mentor_profile_id = :mentor_profile_id AND student_id = :student_id
                AND service_type = :service_type AND status = 'pending'
             LIMIT 1"
        );
        $stmt->execute(['mentor_profile_id' => $mentorProfileId, 'student_id' => $studentId, 'service_type' => $serviceType]);

        return $stmt->fetchColumn() !== false;
    }

    public static function forMentor(int $mentorProfileId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS student_name, u.email AS student_email
             FROM mentorship_requests r
             JOIN users u ON u.id = r.student_id
             WHERE r.mentor_profile_id = :mentor_profile_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['mentor_profile_id' => $mentorProfileId]);

        return $stmt->fetchAll();
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS mentor_name
             FROM mentorship_requests r
             JOIN mentor_profiles mp ON mp.id = r.mentor_profile_id
             JOIN users u ON u.id = mp.user_id
             WHERE r.student_id = :student_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }
}
