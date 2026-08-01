<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteCertificate extends Model
{
    protected static string $table = 'institute_certificates';

    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_certificates WHERE institute_id = :institute_id ORDER BY created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }
}
