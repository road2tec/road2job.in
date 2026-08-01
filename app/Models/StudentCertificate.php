<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class StudentCertificate extends Model
{
    protected static string $table = 'student_certificates';

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM student_certificates WHERE user_id = :user_id ORDER BY issue_date DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
