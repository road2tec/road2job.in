<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class PortfolioView extends Model
{
    protected static string $table = 'portfolio_views';
    protected static bool $timestamps = false;

    public static function record(int $userId, string $ip): string
    {
        return static::insert([
            'user_id' => $userId,
            'ip_address' => $ip,
            'viewed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function countTotal(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM portfolio_views WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public static function countUniqueVisitors(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(DISTINCT ip_address) FROM portfolio_views WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }
}
