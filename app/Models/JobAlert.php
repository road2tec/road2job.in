<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class JobAlert extends Model
{
    protected static string $table = 'job_alerts';
    protected static bool $timestamps = false;

    public static function create(int $studentId, array $filters): string
    {
        return static::insert([
            'student_id' => $studentId,
            'keyword' => $filters['keyword'] ?: null,
            'location' => $filters['location'] ?: null,
            'type' => $filters['type'] ?: null,
            'experience_level' => $filters['experience_level'] ?: null,
            'is_remote' => !empty($filters['is_remote']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM job_alerts WHERE student_id = :student_id ORDER BY created_at DESC"
        );
        $stmt->execute(['student_id' => $studentId]);

        return $stmt->fetchAll();
    }

    public static function all(): array
    {
        return Database::connection()->query("SELECT * FROM job_alerts")->fetchAll();
    }
}
