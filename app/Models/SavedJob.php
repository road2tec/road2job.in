<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class SavedJob extends Model
{
    protected static string $table = 'saved_jobs';
    protected static bool $timestamps = false;

    public static function isSaved(int $studentId, int $jobPostingId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM saved_jobs WHERE student_id = :student_id AND job_posting_id = :job_posting_id LIMIT 1"
        );
        $stmt->execute(['student_id' => $studentId, 'job_posting_id' => $jobPostingId]);

        return $stmt->fetchColumn() !== false;
    }

    public static function save(int $studentId, int $jobPostingId): void
    {
        static::insert([
            'student_id' => $studentId,
            'job_posting_id' => $jobPostingId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function unsave(int $studentId, int $jobPostingId): void
    {
        $stmt = Database::connection()->prepare(
            "DELETE FROM saved_jobs WHERE student_id = :student_id AND job_posting_id = :job_posting_id"
        );
        $stmt->execute(['student_id' => $studentId, 'job_posting_id' => $jobPostingId]);
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, jp.title AS job_title, jp.type AS job_type, jp.location AS job_location, jp.status AS job_status, c.name AS company_name, c.logo_path AS company_logo_path
             FROM saved_jobs s
             JOIN job_postings jp ON jp.id = s.job_posting_id
             JOIN companies c ON c.id = jp.company_id
             WHERE s.student_id = :student_id
             ORDER BY s.created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }
}
