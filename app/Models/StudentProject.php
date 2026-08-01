<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class StudentProject extends Model
{
    protected static string $table = 'student_projects';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM student_projects WHERE user_id = :user_id ORDER BY start_date DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Replaces the full featured-project set for a user in one go (the
     * Portfolio Manager submits the complete checked-id list each time,
     * not a diff) - both statements are scoped to user_id so a tampered
     * id for another student's project can never be marked featured.
     */
    public static function setFeaturedForUser(int $userId, array $featuredIds): void
    {
        $db = Database::connection();
        $db->prepare("UPDATE student_projects SET is_featured = 0 WHERE user_id = :user_id")
            ->execute(['user_id' => $userId]);

        $featuredIds = array_values(array_unique(array_map('intval', $featuredIds)));
        if (empty($featuredIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($featuredIds), '?'));
        $params = $featuredIds;
        $params[] = $userId;
        $stmt = $db->prepare("UPDATE student_projects SET is_featured = 1 WHERE id IN ({$placeholders}) AND user_id = ?");
        $stmt->execute($params);
    }
}
