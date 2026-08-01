<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class StudentAchievement extends Model
{
    protected static string $table = 'student_achievements';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM student_achievements WHERE user_id = :user_id ORDER BY achieved_on DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
