<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class EventRegistration extends Model
{
    protected static string $table = 'event_registrations';

    public static function create(int $eventId, int $userId): string
    {
        return static::insert([
            'event_id' => $eventId,
            'user_id' => $userId,
            'status' => 'registered',
            'registered_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function hasRegistered(int $eventId, int $userId): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT 1 FROM event_registrations WHERE event_id = :event_id AND user_id = :user_id LIMIT 1"
        );
        $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);

        return $stmt->fetchColumn() !== false;
    }

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.full_name AS registrant_name, u.email AS registrant_email
             FROM event_registrations r
             JOIN users u ON u.id = r.user_id
             WHERE r.event_id = :event_id
             ORDER BY r.registered_at DESC"
        );
        $stmt->execute(['event_id' => $eventId]);

        return $stmt->fetchAll();
    }

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, e.title AS event_title, e.category, e.starts_at, e.organizer_user_id
             FROM event_registrations r
             JOIN events e ON e.id = r.event_id
             WHERE r.user_id = :user_id
             ORDER BY e.starts_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function findForEventAndUser(int $eventId, int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM event_registrations WHERE event_id = :event_id AND user_id = :user_id LIMIT 1"
        );
        $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function markAttended(int $id): void
    {
        static::update($id, ['status' => 'attended']);
    }
}
