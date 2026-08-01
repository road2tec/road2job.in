<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class PasswordReset extends Model
{
    protected static string $table = 'password_resets';
    protected static bool $timestamps = false;

    public static function create(string $email, string $tokenHash, int $expiryMinutes): string
    {
        return static::insert([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', time() + $expiryMinutes * 60),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function findValidByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM password_resets
             WHERE email = :email AND used_at IS NULL AND expires_at >= :now
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['email' => $email, 'now' => date('Y-m-d H:i:s')]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function markUsed(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE password_resets SET used_at = :now WHERE id = :id"
        );

        return $stmt->execute(['id' => $id, 'now' => date('Y-m-d H:i:s')]);
    }
}
