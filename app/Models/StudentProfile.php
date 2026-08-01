<?php

namespace App\Models;

use Core\Model;

class StudentProfile extends Model
{
    protected static string $table = 'student_profiles';

    public static function findByUserId(int $userId): ?array
    {
        return static::findBy('user_id', $userId);
    }

    public static function saveForUser(int $userId, array $data): void
    {
        $existing = static::findByUserId($userId);

        if ($existing === null) {
            static::insert(array_merge($data, ['user_id' => $userId]));
            return;
        }

        static::update((int) $existing['id'], $data);
    }
}
