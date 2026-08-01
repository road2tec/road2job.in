<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class StudentEducation extends Model
{
    protected static string $table = 'student_education';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM student_education WHERE user_id = :user_id ORDER BY start_date DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
