<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CommunityPost extends Model
{
    protected static string $table = 'community_posts';

    public static function forCategory(?string $category, int $limit = 50, ?int $page = null, int $perPage = 24): array
    {
        $where = $category !== null ? 'WHERE p.category = :category' : '';
        $params = [];

        if ($category !== null) {
            $params['category'] = $category;
        }

        if ($page === null) {
            $sql = "SELECT p.*, u.full_name AS author_name
                    FROM community_posts p
                    JOIN users u ON u.id = p.user_id
                    {$where}
                    ORDER BY p.created_at DESC LIMIT " . (int) $limit;

            $stmt = Database::connection()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM community_posts p {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT p.*, u.full_name AS author_name
             FROM community_posts p
             JOIN users u ON u.id = p.user_id
             {$where}
             ORDER BY p.created_at DESC
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

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM community_posts WHERE user_id = :user_id ORDER BY created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function findWithAuthor(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT p.*, u.full_name AS author_name
             FROM community_posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function incrementViews(int $id, ?int $viewerId): void
    {
        if ($viewerId === null) {
            $stmt = Database::connection()->prepare("UPDATE community_posts SET views = views + 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return;
        }

        $stmt = Database::connection()->prepare(
            "UPDATE community_posts SET views = views + 1 WHERE id = :id AND user_id != :viewer_id"
        );
        $stmt->execute(['id' => $id, 'viewer_id' => $viewerId]);
    }

    public static function markAcceptedReply(int $postId, int $replyId): void
    {
        static::update($postId, ['accepted_reply_id' => $replyId]);
    }
}
