<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CollegeDriveRegistration extends Model
{
    protected static string $table = 'college_drive_registrations';

    public static function forCollege(int $collegeId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS student_name, u.email AS student_email, d.company_name AS drive_company_name
             FROM college_drive_registrations r
             JOIN users u ON u.id = r.student_id
             JOIN college_campus_drives d ON d.id = r.drive_id
             WHERE r.college_id = :college_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['college_id' => $collegeId]);

        return $stmt->fetchAll();
    }

    public static function countByStatus(): array
    {
        $stmt = Database::connection()->query("SELECT status, COUNT(*) AS total FROM college_drive_registrations GROUP BY status");

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int) $row['total'];
        }

        return $result;
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, d.company_name AS drive_company_name, c.name AS college_name
             FROM college_drive_registrations r
             JOIN college_campus_drives d ON d.id = r.drive_id
             JOIN colleges c ON c.id = r.college_id
             WHERE r.student_id = :student_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    public static function hasActiveRequest(int $driveId, int $studentId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM college_drive_registrations
             WHERE drive_id = :drive_id AND student_id = :student_id AND status IN ('pending', 'shortlisted', 'selected')
             LIMIT 1"
        );
        $stmt->execute(['drive_id' => $driveId, 'student_id' => $studentId]);

        return $stmt->fetchColumn() !== false;
    }

    public static function create(int $collegeId, int $driveId, int $studentId, ?string $message): string
    {
        return static::insert([
            'college_id' => $collegeId,
            'drive_id' => $driveId,
            'student_id' => $studentId,
            'status' => 'pending',
            'message' => $message,
        ]);
    }
}
