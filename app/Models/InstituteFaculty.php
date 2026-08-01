<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteFaculty extends Model
{
    protected static string $table = 'institute_faculty';

    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_faculty WHERE institute_id = :institute_id ORDER BY created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }
}
