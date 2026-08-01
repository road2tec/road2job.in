<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CollegeAlumnus extends Model
{
    protected static string $table = 'college_alumni';

    public static function forCollege(int $collegeId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM college_alumni WHERE college_id = :college_id ORDER BY batch_year DESC, created_at DESC"
        );
        $stmt->execute(['college_id' => $collegeId]);

        return $stmt->fetchAll();
    }
}
