<?php

namespace Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = true;

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function find(int|string $id): ?array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";

        if (static::$softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE {$column} = :value";

        if (static::$softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }

        $sql .= " LIMIT 1";

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['value' => $value]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function all(): array
    {
        $sql = "SELECT * FROM " . static::$table;

        if (static::$softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }

        return static::db()->query($sql)->fetchAll();
    }

    public static function where(string $column, mixed $value): array
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE {$column} = :value";

        if (static::$softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = static::db()->prepare($sql);
        $stmt->execute(['value' => $value]);

        return $stmt->fetchAll();
    }

    public static function insert(array $data): string
    {
        if (static::$timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $data['created_at'] ?? $now;
            $data['updated_at'] = $data['updated_at'] ?? $now;
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn ($col) => ":{$col}", $columns);

        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);

        return static::db()->lastInsertId();
    }

    public static function update(int|string $id, array $data): bool
    {
        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $assignments = implode(', ', array_map(fn ($col) => "{$col} = :{$col}", array_keys($data)));

        $sql = "UPDATE " . static::$table . " SET {$assignments} WHERE " . static::$primaryKey . " = :__id";

        $data['__id'] = $id;

        $stmt = static::db()->prepare($sql);

        return $stmt->execute($data);
    }

    public static function delete(int|string $id): bool
    {
        if (static::$softDeletes) {
            return static::update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        }

        $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
        $stmt = static::db()->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public static function count(): int
    {
        $sql = "SELECT COUNT(*) FROM " . static::$table;

        if (static::$softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }

        return (int) static::db()->query($sql)->fetchColumn();
    }
}
