<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class BlogPost extends Model
{
    protected static string $table = 'blog_posts';

    public static function publishedListing(): array
    {
        $stmt = Database::connection()->query(
            "SELECT p.*, u.full_name AS author_name
             FROM blog_posts p JOIN users u ON u.id = p.author_id
             WHERE p.status = 'published'
             ORDER BY p.published_at DESC"
        );

        return $stmt->fetchAll();
    }

    public static function findPublished(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT p.*, u.full_name AS author_name
             FROM blog_posts p JOIN users u ON u.id = p.author_id
             WHERE p.id = :id AND p.status = 'published'"
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
            $where = '(p.title LIKE :keyword_title OR u.full_name LIKE :keyword_author)';
            $params['keyword_title'] = '%' . $keyword . '%';
            $params['keyword_author'] = '%' . $keyword . '%';
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM blog_posts p JOIN users u ON u.id = p.author_id WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT p.*, u.full_name AS author_name
             FROM blog_posts p JOIN users u ON u.id = p.author_id
             WHERE {$where}
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
}
