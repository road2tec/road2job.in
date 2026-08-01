<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class StudentExperience extends Model
{
    protected static string $table = 'student_experience';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM student_experience WHERE user_id = :user_id ORDER BY start_date DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
