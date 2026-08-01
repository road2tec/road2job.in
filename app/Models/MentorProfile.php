<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class MentorProfile extends Model
{
    protected static string $table = 'mentor_profiles';

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

    public static function publicListing(?int $page = null, int $perPage = 24): array
    {
        if ($page === null) {
            $stmt = Database::connection()->prepare(
                "SELECT mp.*, u.full_name
                 FROM mentor_profiles mp
                 JOIN users u ON u.id = mp.user_id
                 WHERE mp.expertise IS NOT NULL
                 ORDER BY mp.created_at DESC"
            );
            $stmt->execute();

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM mentor_profiles mp WHERE mp.expertise IS NOT NULL"
        );
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT mp.*, u.full_name
             FROM mentor_profiles mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.expertise IS NOT NULL
             ORDER BY mp.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function findWithUser(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT mp.*, u.full_name
             FROM mentor_profiles mp
             JOIN users u ON u.id = mp.user_id
             WHERE mp.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
