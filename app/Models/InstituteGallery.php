<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteGallery extends Model
{
    protected static string $table = 'institute_gallery';

    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_gallery WHERE institute_id = :institute_id ORDER BY created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }
}
