<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Event extends Model
{
    protected static string $table = 'events';

    public static function forOrganizer(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM events WHERE organizer_user_id = :organizer_user_id ORDER BY created_at DESC"
        );
        $stmt->execute(['organizer_user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function publicListing(?string $category = null, ?int $page = null, int $perPage = 24): array
    {
        $where = "e.status = 'published'";
        $params = [];

        if ($category !== null) {
            $where .= " AND e.category = :category";
            $params['category'] = $category;
        }

        if ($page === null) {
            $stmt = Database::connection()->prepare(
                "SELECT e.*, u.full_name AS organizer_name
                 FROM events e
                 JOIN users u ON u.id = e.organizer_user_id
                 WHERE {$where}
                 ORDER BY e.starts_at ASC"
            );
            $stmt->execute($params);

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM events e WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT e.*, u.full_name AS organizer_name
             FROM events e
             JOIN users u ON u.id = e.organizer_user_id
             WHERE {$where}
             ORDER BY e.starts_at ASC
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

    public static function findPublished(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT e.*, u.full_name AS organizer_name
             FROM events e
             JOIN users u ON u.id = e.organizer_user_id
             WHERE e.id = :id AND e.status = 'published'"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function adminListing(?string $keyword = null, int $page = 1, int $perPage = 20): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($keyword)) {
            $where = '(e.title LIKE :keyword_title OR u.full_name LIKE :keyword_organizer)';
            $params['keyword_title'] = '%' . $keyword . '%';
            $params['keyword_organizer'] = '%' . $keyword . '%';
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM events e JOIN users u ON u.id = e.organizer_user_id WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT e.*, u.full_name AS organizer_name
             FROM events e
             JOIN users u ON u.id = e.organizer_user_id
             WHERE {$where}
             ORDER BY e.created_at DESC
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
