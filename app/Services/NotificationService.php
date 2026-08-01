<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function notify(int $userId, string $title, string $message, string $type = 'general'): void
    {
        Notification::push($userId, $title, $message, $type);
    }

    public function recentFor(int $userId, int $limit = 10): array
    {
        return Notification::forUser($userId, $limit);
    }

    public function unreadCount(int $userId): int
    {
        return Notification::unreadCount($userId);
    }

    public function markRead(int $id): void
    {
        Notification::markRead($id);
    }
}
