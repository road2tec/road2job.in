<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class ResearchItem extends Model
{
    protected static string $table = 'research_items';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM research_items WHERE user_id = :user_id ORDER BY created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function publicListing(?string $type = null, int $limit = 50, ?int $page = null, int $perPage = 24): array
    {
        $where = "sp.profile_visibility = 'public'";
        $params = [];

        if ($type !== null) {
            $where .= " AND r.type = :type";
            $params['type'] = $type;
        }

        if ($page === null) {
            $sql = "SELECT r.*, u.full_name AS author_name
                    FROM research_items r
                    JOIN users u ON u.id = r.user_id
                    JOIN student_profiles sp ON sp.user_id = r.user_id
                    WHERE {$where}
                    ORDER BY r.created_at DESC LIMIT " . (int) $limit;

            $stmt = Database::connection()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM research_items r JOIN student_profiles sp ON sp.user_id = r.user_id WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS author_name
             FROM research_items r
             JOIN users u ON u.id = r.user_id
             JOIN student_profiles sp ON sp.user_id = r.user_id
             WHERE {$where}
             ORDER BY r.created_at DESC
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

    public static function findPublicWithAuthor(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS author_name, u.username AS author_username
             FROM research_items r
             JOIN users u ON u.id = r.user_id
             JOIN student_profiles sp ON sp.user_id = r.user_id
             WHERE r.id = :id AND sp.profile_visibility = 'public'"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
