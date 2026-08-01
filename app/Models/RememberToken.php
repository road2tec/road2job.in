<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class RememberToken extends Model
{
    protected static string $table = 'remember_tokens';
    protected static bool $timestamps = false;

    public static function create(int $userId, string $selector, string $tokenHash, int $expiryDays): string
    {
        return static::insert([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', time() + $expiryDays * 86400),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function findValidBySelector(string $selector): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM remember_tokens WHERE selector = :selector AND expires_at >= :now LIMIT 1"
        );
        $stmt->execute(['selector' => $selector, 'now' => date('Y-m-d H:i:s')]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function deleteBySelector(string $selector): bool
    {
        $stmt = Database::connection()->prepare("DELETE FROM remember_tokens WHERE selector = :selector");

        return $stmt->execute(['selector' => $selector]);
    }

    public static function deleteAllForUser(int $userId): bool
    {
        $stmt = Database::connection()->prepare("DELETE FROM remember_tokens WHERE user_id = :user_id");

        return $stmt->execute(['user_id' => $userId]);
    }
}
