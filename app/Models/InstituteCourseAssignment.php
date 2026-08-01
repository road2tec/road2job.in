<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteCourseAssignment extends Model
{
    protected static string $table = 'institute_course_assignments';

    public static function forCourse(int $courseId, ?int $studentId = null): array
    {
        if ($studentId === null) {
            $stmt = Database::connection()->prepare(
                "SELECT * FROM institute_course_assignments WHERE course_id = :course_id ORDER BY created_at DESC"
            );
            $stmt->execute(['course_id' => $courseId]);

            return $stmt->fetchAll();
        }

        $stmt = Database::connection()->prepare(
            "SELECT a.*,
                    s.id AS submission_id, s.submission_text AS submission_submission_text,
                    s.submission_file_path AS submission_submission_file_path,
                    s.status AS submission_status, s.feedback AS submission_feedback,
                    s.submitted_at AS submission_submitted_at
             FROM institute_course_assignments a
             LEFT JOIN institute_assignment_submissions s
                 ON s.assignment_id = a.id AND s.student_id = :student_id
             WHERE a.course_id = :course_id
             ORDER BY a.created_at DESC"
        );
        $stmt->execute(['course_id' => $courseId, 'student_id' => $studentId]);

        return array_map(static function (array $row): array {
            $mySubmission = $row['submission_id'] !== null ? [
                'id' => $row['submission_id'],
                'submission_text' => $row['submission_submission_text'],
                'submission_file_path' => $row['submission_submission_file_path'],
                'status' => $row['submission_status'],
                'feedback' => $row['submission_feedback'],
                'submitted_at' => $row['submission_submitted_at'],
            ] : null;

            foreach (array_keys($row) as $key) {
                if (str_starts_with($key, 'submission_')) {
                    unset($row[$key]);
                }
            }

            $row['mySubmission'] = $mySubmission;

            return $row;
        }, $stmt->fetchAll());
    }
}
