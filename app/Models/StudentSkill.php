<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class StudentSkill extends Model
{
    protected static string $table = 'student_skills';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM student_skills WHERE user_id = :user_id ORDER BY skill_name ASC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Lowercased, trimmed skill_name => true, for case-insensitive dedupe
     * against a student's existing skills (bulk add must not let "Java"/
     * "java"/"JAVA" become 3 separate rows). Trims in SQL, not just case-
     * folds, since a handful of older rows predate this project always
     * trimming on save and carry incidental leading/trailing whitespace -
     * comparing against those un-trimmed would silently defeat the dedupe.
     */
    public static function existingNamesLower(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT LOWER(TRIM(skill_name)) AS name_lower FROM student_skills WHERE user_id = :user_id"
        );
        $stmt->execute(['user_id' => $userId]);

        return array_fill_keys(array_column($stmt->fetchAll(), 'name_lower'), true);
    }
}
