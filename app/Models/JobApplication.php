<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class JobApplication extends Model
{
    protected static string $table = 'job_applications';

    public static function hasApplied(int $jobPostingId, int $studentId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM job_applications WHERE job_posting_id = :job_posting_id AND student_id = :student_id LIMIT 1"
        );
        $stmt->execute(['job_posting_id' => $jobPostingId, 'student_id' => $studentId]);

        return $stmt->fetchColumn() !== false;
    }

    public static function create(int $jobPostingId, int $studentId, int $companyId, string $resumeSnapshot, ?string $coverNote): string
    {
        return static::insert([
            'job_posting_id' => $jobPostingId,
            'student_id' => $studentId,
            'company_id' => $companyId,
            'resume_snapshot' => $resumeSnapshot,
            'cover_note' => $coverNote,
            'status' => 'applied',
        ]);
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT a.*, jp.title AS job_title, jp.description AS job_description, jp.requirements AS job_requirements,
                    jp.type AS job_type, jp.location AS job_location, jp.is_remote AS job_is_remote,
                    jp.min_salary AS job_min_salary, jp.max_salary AS job_max_salary,
                    c.name AS company_name, c.logo_path AS company_logo_path
             FROM job_applications a
             JOIN job_postings jp ON jp.id = a.job_posting_id
             JOIN companies c ON c.id = a.company_id
             WHERE a.student_id = :student_id
             ORDER BY a.created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    public static function countByStatus(): array
    {
        $stmt = Database::connection()->query("SELECT status, COUNT(*) AS total FROM job_applications GROUP BY status");

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int) $row['total'];
        }

        return $result;
    }

    public static function adminListing(?string $keyword = null, int $page = 1, int $perPage = 20): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($keyword)) {
            $where = '(jp.title LIKE :keyword_job OR c.name LIKE :keyword_company OR u.full_name LIKE :keyword_student OR u.email LIKE :keyword_email)';
            $params['keyword_job'] = '%' . $keyword . '%';
            $params['keyword_company'] = '%' . $keyword . '%';
            $params['keyword_student'] = '%' . $keyword . '%';
            $params['keyword_email'] = '%' . $keyword . '%';
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM job_applications a
             JOIN job_postings jp ON jp.id = a.job_posting_id
             JOIN companies c ON c.id = a.company_id
             JOIN users u ON u.id = a.student_id
             WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT a.*, jp.title AS job_title, c.name AS company_name, u.full_name AS student_name, u.email AS student_email
             FROM job_applications a
             JOIN job_postings jp ON jp.id = a.job_posting_id
             JOIN companies c ON c.id = a.company_id
             JOIN users u ON u.id = a.student_id
             WHERE {$where}
             ORDER BY a.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function forCompany(int $companyId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT a.*, jp.title AS job_title, u.full_name AS student_name, u.email AS student_email
             FROM job_applications a
             JOIN job_postings jp ON jp.id = a.job_posting_id
             JOIN users u ON u.id = a.student_id
             WHERE a.company_id = :company_id
             ORDER BY a.created_at DESC"
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }
}
