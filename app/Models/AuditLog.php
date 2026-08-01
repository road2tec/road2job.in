<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';
    protected static bool $timestamps = false;

    public static function record(?int $userId, string $action, string $description = '', string $ip = ''): string
    {
        return static::insert([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function recent(int $limit = 200, ?string $actionLike = null): array
    {
        $where = '1=1';
        $params = [];

        if ($actionLike !== null && $actionLike !== '') {
            $where = 'l.action LIKE :action_like';
            $params['action_like'] = '%' . $actionLike . '%';
        }

        $sql = "SELECT l.*, u.full_name AS actor_name, u.email AS actor_email
                FROM audit_logs l
                LEFT JOIN users u ON u.id = l.user_id
                WHERE {$where}
                ORDER BY l.id DESC
                LIMIT " . (int) $limit;

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Paginated version of recent() for the full admin Audit Logs page -
     * recent() itself stays as-is (still used unpaginated for the Security
     * page's smaller "recent activity" widget, where a simple capped list
     * is genuinely all that's needed).
     */
    public static function adminListing(?string $actionLike = null, int $page = 1, int $perPage = 20): array
    {
        $where = '1=1';
        $params = [];

        if ($actionLike !== null && $actionLike !== '') {
            $where = 'l.action LIKE :action_like';
            $params['action_like'] = '%' . $actionLike . '%';
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM audit_logs l WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT l.*, u.full_name AS actor_name, u.email AS actor_email
             FROM audit_logs l
             LEFT JOIN users u ON u.id = l.user_id
             WHERE {$where}
             ORDER BY l.id DESC
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
