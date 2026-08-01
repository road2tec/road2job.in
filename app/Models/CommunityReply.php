<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CommunityReply extends Model
{
    protected static string $table = 'community_replies';

    public static function createForPost(int $postId, int $userId, string $body): string
    {
        return static::insert([
            'community_post_id' => $postId,
            'user_id' => $userId,
            'body' => $body,
        ]);
    }

    public static function forPost(int $postId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS author_name
             FROM community_replies r
             JOIN users u ON u.id = r.user_id
             WHERE r.community_post_id = :post_id
             ORDER BY r.created_at ASC"
        );
        $stmt->execute(['post_id' => $postId]);

        return $stmt->fetchAll();
    }
}
