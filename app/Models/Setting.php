<?php

namespace App\Models;

use Core\Database;

class Setting
{
    public static function all(): array
    {
        $stmt = Database::connection()->query("SELECT setting_key, setting_value FROM settings");

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }

        return $result;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $stmt = Database::connection()->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();

        return $value === false ? $default : $value;
    }

    public static function set(string $key, ?string $value): void
    {
        $stmt = Database::connection()->prepare(
            "INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (:key, :value, :now)
             ON DUPLICATE KEY UPDATE setting_value = :value2, updated_at = :now2"
        );
        $now = date('Y-m-d H:i:s');
        $stmt->execute(['key' => $key, 'value' => $value, 'now' => $now, 'value2' => $value, 'now2' => $now]);
    }
}
