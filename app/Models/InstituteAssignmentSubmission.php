<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteAssignmentSubmission extends Model
{
    protected static string $table = 'institute_assignment_submissions';

    public static function findByAssignmentAndStudent(int $assignmentId, int $studentId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_assignment_submissions WHERE assignment_id = :assignment_id AND student_id = :student_id LIMIT 1"
        );
        $stmt->execute(['assignment_id' => $assignmentId, 'student_id' => $studentId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function submit(int $assignmentId, int $studentId, ?string $text, ?string $filePath): void
    {
        $existing = self::findByAssignmentAndStudent($assignmentId, $studentId);

        $data = [
            'submission_text' => $text,
            'status' => 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        if ($filePath !== null) {
            $data['submission_file_path'] = $filePath;
        }

        if ($existing === null) {
            $data['assignment_id'] = $assignmentId;
            $data['student_id'] = $studentId;
            static::insert($data);
            return;
        }

        // Resubmitting clears prior feedback - it's a new answer, not the old graded one.
        $data['feedback'] = null;
        $data['reviewed_at'] = null;

        static::update((int) $existing['id'], $data);
    }

    public static function forAssignment(int $assignmentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, u.full_name AS student_name
             FROM institute_assignment_submissions s
             JOIN users u ON u.id = s.student_id
             WHERE s.assignment_id = :assignment_id
             ORDER BY s.submitted_at DESC"
        );
        $stmt->execute(['assignment_id' => $assignmentId]);

        return $stmt->fetchAll();
    }

    public static function markReviewed(int $id, string $feedback): void
    {
        static::update($id, [
            'status' => 'reviewed',
            'feedback' => $feedback,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
