<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Company extends Model
{
    protected static string $table = 'companies';

    public static function findByUserId(int $userId): ?array
    {
        return static::findBy('user_id', $userId);
    }

    public static function saveForUser(int $userId, array $data): void
    {
        $existing = static::findByUserId($userId);

        if ($existing === null) {
            static::insert(array_merge($data, ['user_id' => $userId]));
            return;
        }

        static::update((int) $existing['id'], $data);
    }

    public static function markVerificationPending(int $userId, ?string $documentPath): void
    {
        $data = ['verification_status' => 'pending'];

        if ($documentPath !== null) {
            $data['verification_document_path'] = $documentPath;
        }

        static::saveForUser($userId, $data);
    }

    public static function publicListing(?int $page = null, int $perPage = 24): array
    {
        if ($page === null) {
            $stmt = Database::connection()->prepare(
                "SELECT * FROM companies WHERE name IS NOT NULL ORDER BY created_at DESC"
            );
            $stmt->execute();

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM companies WHERE name IS NOT NULL"
        );
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT * FROM companies WHERE name IS NOT NULL ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function adminListing(?string $keyword = null, int $page = 1, int $perPage = 20): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($keyword)) {
            $where = '(c.name LIKE :keyword_name OR u.full_name LIKE :keyword_owner OR u.email LIKE :keyword_email)';
            $params['keyword_name'] = '%' . $keyword . '%';
            $params['keyword_owner'] = '%' . $keyword . '%';
            $params['keyword_email'] = '%' . $keyword . '%';
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM companies c JOIN users u ON u.id = c.user_id WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT c.*, u.full_name AS owner_name, u.email AS owner_email, u.status AS owner_status
             FROM companies c JOIN users u ON u.id = c.user_id
             WHERE {$where}
             ORDER BY c.created_at DESC
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

    public static function setVerificationStatus(int $id, string $status): bool
    {
        return static::update($id, ['verification_status' => $status]);
    }
}
