<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteEnrollmentRequest extends Model
{
    protected static string $table = 'institute_enrollment_requests';

    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS student_name, u.email AS student_email, c.title AS course_title
             FROM institute_enrollment_requests r
             JOIN users u ON u.id = r.student_id
             JOIN institute_courses c ON c.id = r.course_id
             WHERE r.institute_id = :institute_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, c.title AS course_title, i.name AS institute_name
             FROM institute_enrollment_requests r
             JOIN institute_courses c ON c.id = r.course_id
             JOIN institutes i ON i.id = r.institute_id
             WHERE r.student_id = :student_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    public static function hasActiveRequest(int $courseId, int $studentId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM institute_enrollment_requests
             WHERE course_id = :course_id AND student_id = :student_id AND status IN ('pending', 'contacted', 'enrolled', 'completed')
             LIMIT 1"
        );
        $stmt->execute(['course_id' => $courseId, 'student_id' => $studentId]);

        return $stmt->fetchColumn() !== false;
    }

    public static function isEnrolled(int $courseId, int $studentId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM institute_enrollment_requests
             WHERE course_id = :course_id AND student_id = :student_id AND status IN ('enrolled', 'completed')
             LIMIT 1"
        );
        $stmt->execute(['course_id' => $courseId, 'student_id' => $studentId]);

        return $stmt->fetchColumn() !== false;
    }

    public static function findEnrollment(int $courseId, int $studentId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_enrollment_requests
             WHERE course_id = :course_id AND student_id = :student_id AND status IN ('enrolled', 'completed')
             LIMIT 1"
        );
        $stmt->execute(['course_id' => $courseId, 'student_id' => $studentId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function forStudentEnrollments(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, c.title AS course_title, c.format AS course_format, i.name AS institute_name
             FROM institute_enrollment_requests r
             JOIN institute_courses c ON c.id = r.course_id
             JOIN institutes i ON i.id = r.institute_id
             WHERE r.student_id = :student_id AND r.status IN ('enrolled', 'completed')
             ORDER BY r.updated_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    public static function create(int $instituteId, int $courseId, int $studentId, ?string $message): string
    {
        return static::insert([
            'institute_id' => $instituteId,
            'course_id' => $courseId,
            'student_id' => $studentId,
            'status' => 'pending',
            'message' => $message,
        ]);
    }
}
