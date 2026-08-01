<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class StudentLanguage extends Model
{
    protected static string $table = 'student_languages';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM student_languages WHERE user_id = :user_id ORDER BY language_name ASC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
