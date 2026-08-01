<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CollegeDepartmentStat extends Model
{
    protected static string $table = 'college_department_stats';

    public static function forCollege(int $collegeId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM college_department_stats WHERE college_id = :college_id ORDER BY academic_year DESC, department_name ASC"
        );
        $stmt->execute(['college_id' => $collegeId]);

        return $stmt->fetchAll();
    }
}
