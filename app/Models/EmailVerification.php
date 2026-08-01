<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class EmailVerification extends Model
{
    protected static string $table = 'email_verifications';
    protected static bool $timestamps = false;

    public static function create(int $userId, string $email, string $tokenHash, int $expiryMinutes): string
    {
        return static::insert([
            'user_id' => $userId,
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', time() + $expiryMinutes * 60),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function latestFor(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM email_verifications
             WHERE user_id = :user_id AND verified_at IS NULL
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function markVerified(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE email_verifications SET verified_at = :now WHERE id = :id"
        );

        return $stmt->execute(['id' => $id, 'now' => date('Y-m-d H:i:s')]);
    }
}
