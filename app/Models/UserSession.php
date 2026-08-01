<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class UserSession extends Model
{
    protected static string $table = 'user_sessions';
    protected static bool $timestamps = false;

    public static function open(int $userId, string $ip, string $userAgent, ?string $sessionId = null): string
    {
        return static::insert([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function close(int $id): bool
    {
        return static::update($id, ['logout_at' => date('Y-m-d H:i:s')]);
    }

    public static function historyFor(int $userId): array
    {
        return static::where('user_id', $userId);
    }

    public static function activeForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM user_sessions WHERE user_id = :user_id AND logout_at IS NULL ORDER BY login_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function findBySessionId(string $sessionId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM user_sessions WHERE session_id = :session_id ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['session_id' => $sessionId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function closeAllForUserExcept(int $userId, ?int $exceptId = null): bool
    {
        $sql = "UPDATE user_sessions SET logout_at = :now WHERE user_id = :user_id AND logout_at IS NULL";
        $params = ['now' => date('Y-m-d H:i:s'), 'user_id' => $userId];

        if ($exceptId !== null) {
            $sql .= " AND id != :except_id";
            $params['except_id'] = $exceptId;
        }

        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute($params);
    }
}
