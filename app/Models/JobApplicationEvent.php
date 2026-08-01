<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class JobApplicationEvent extends Model
{
    protected static string $table = 'job_application_events';
    protected static bool $timestamps = false;

    public static function record(int $applicationId, string $eventType, ?string $fromStatus = null, ?string $toStatus = null): void
    {
        $now = date('Y-m-d H:i:s');

        static::insert([
            'job_application_id' => $applicationId,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'event_at' => $now,
            'created_at' => $now,
        ]);
    }

    public static function forApplication(int $applicationId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM job_application_events WHERE job_application_id = :id ORDER BY event_at ASC, id ASC"
        );
        $stmt->execute(['id' => $applicationId]);

        return $stmt->fetchAll();
    }

    public static function hasEventType(int $applicationId, string $eventType): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM job_application_events WHERE job_application_id = :id AND event_type = :type LIMIT 1"
        );
        $stmt->execute(['id' => $applicationId, 'type' => $eventType]);

        return $stmt->fetchColumn() !== false;
    }
}
