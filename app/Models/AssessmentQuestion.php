<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class AssessmentQuestion extends Model
{
    protected static string $table = 'assessment_questions';

    public static function randomForCategory(string $category, int $count = 5): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM assessment_questions WHERE category = :category ORDER BY RAND() LIMIT " . (int) $count
        );
        $stmt->execute(['category' => $category]);

        return $stmt->fetchAll();
    }
}
