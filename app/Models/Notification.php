<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static bool $timestamps = false;

    public static function push(int $userId, string $title, string $message, string $type = 'general'): string
    {
        return static::insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function forUser(int $userId, int $limit = 10): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY id DESC LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public static function markRead(int $id): bool
    {
        return static::update($id, ['is_read' => 1]);
    }
}
