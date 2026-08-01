<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CollegeCampusDrive extends Model
{
    protected static string $table = 'college_campus_drives';

    public static function forCollege(int $collegeId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM college_campus_drives WHERE college_id = :college_id ORDER BY created_at DESC"
        );
        $stmt->execute(['college_id' => $collegeId]);

        return $stmt->fetchAll();
    }

    public static function publishedForCollege(int $collegeId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM college_campus_drives WHERE college_id = :college_id AND status = 'published' ORDER BY drive_date ASC"
        );
        $stmt->execute(['college_id' => $collegeId]);

        return $stmt->fetchAll();
    }
}
