<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class JobPosting extends Model
{
    protected static string $table = 'job_postings';

    public static function forCompany(int $companyId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM job_postings WHERE company_id = :company_id ORDER BY created_at DESC"
        );
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    public static function countPublishedForCompany(int $companyId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM job_postings WHERE company_id = :company_id AND status = 'published'"
        );
        $stmt->execute(['company_id' => $companyId]);

        return (int) $stmt->fetchColumn();
    }

    public static function publishedListing(int $limit = 50): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT jp.*, c.name AS company_name, c.logo_path AS company_logo_path
             FROM job_postings jp
             JOIN companies c ON c.id = jp.company_id
             WHERE jp.status = 'published'
             ORDER BY jp.published_at DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function findPublished(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT jp.*, c.name AS company_name, c.logo_path AS company_logo_path, c.headquarters_location AS company_location
             FROM job_postings jp
             JOIN companies c ON c.id = jp.company_id
             WHERE jp.id = :id AND jp.status = 'published'"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function countByStatus(): array
    {
        $stmt = Database::connection()->query("SELECT status, COUNT(*) AS total FROM job_postings GROUP BY status");

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int) $row['total'];
        }

        return $result;
    }

    public static function adminListing(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'jp.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['keyword'])) {
            $where[] = 'jp.title LIKE :keyword';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM job_postings jp JOIN companies c ON c.id = jp.company_id WHERE {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT jp.*, c.name AS company_name
                FROM job_postings jp
                JOIN companies c ON c.id = jp.company_id
                WHERE {$whereSql}
                ORDER BY jp.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function search(array $filters, ?string $forceType = null, ?int $page = null, int $perPage = 24): array
    {
        $where = ["jp.status = 'published'"];
        $params = [];

        $type = $forceType ?? ($filters['type'] ?? '');
        if ($type !== '') {
            $where[] = 'jp.type = :type';
            $params['type'] = $type;
        }

        if (!empty($filters['location'])) {
            $where[] = 'jp.location LIKE :location';
            $params['location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['experience_level'])) {
            $where[] = 'jp.experience_level = :experience_level';
            $params['experience_level'] = $filters['experience_level'];
        }

        if (!empty($filters['is_remote'])) {
            $where[] = 'jp.is_remote = 1';
        }

        if (!empty($filters['keyword'])) {
            $where[] = '(jp.title LIKE :keyword_title OR jp.description LIKE :keyword_description)';
            $params['keyword_title'] = '%' . $filters['keyword'] . '%';
            $params['keyword_description'] = '%' . $filters['keyword'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        if ($page === null) {
            $sql = "SELECT jp.*, c.name AS company_name, c.logo_path AS company_logo_path
                    FROM job_postings jp
                    JOIN companies c ON c.id = jp.company_id
                    WHERE {$whereSql}
                    ORDER BY jp.published_at DESC
                    LIMIT 100";

            $stmt = Database::connection()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM job_postings jp JOIN companies c ON c.id = jp.company_id WHERE {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT jp.*, c.name AS company_name, c.logo_path AS company_logo_path
             FROM job_postings jp
             JOIN companies c ON c.id = jp.company_id
             WHERE {$whereSql}
             ORDER BY jp.published_at DESC
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
}
