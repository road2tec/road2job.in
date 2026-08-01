<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InterviewSession extends Model
{
    protected static string $table = 'interview_sessions';

    public static function create(int $jobApplicationId, int $companyId, int $studentId): string
    {
        return static::insert([
            'job_application_id' => $jobApplicationId,
            'company_id' => $companyId,
            'student_id' => $studentId,
            'status' => 'pending',
            'requested_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function findByApplication(int $jobApplicationId): ?array
    {
        return static::findBy('job_application_id', $jobApplicationId);
    }

    public static function forCompany(int $companyId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, jp.title AS job_title, u.full_name AS student_name, u.email AS student_email
             FROM interview_sessions s
             JOIN job_applications a ON a.id = s.job_application_id
             JOIN job_postings jp ON jp.id = a.job_posting_id
             JOIN users u ON u.id = s.student_id
             WHERE s.company_id = :company_id
             ORDER BY s.created_at DESC"
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, jp.title AS job_title, c.name AS company_name
             FROM interview_sessions s
             JOIN job_applications a ON a.id = s.job_application_id
             JOIN job_postings jp ON jp.id = a.job_posting_id
             JOIN companies c ON c.id = s.company_id
             WHERE s.student_id = :student_id
             ORDER BY s.created_at DESC"
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
