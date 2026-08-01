<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class User extends Model
{
    protected static string $table = 'users';
    protected static bool $softDeletes = true;
    protected static bool $timestamps = true;

    public static function findByEmail(string $email): ?array
    {
        return static::findBy('email', $email);
    }

    public static function findByPhone(string $phone): ?array
    {
        return static::findBy('phone', $phone);
    }

    public static function findByIdentifier(string $identifier): ?array
    {
        return filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? static::findByEmail($identifier)
            : static::findByPhone($identifier);
    }

    public static function findWithRole(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT u.*, r.slug AS role_slug, r.label AS role_label
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id AND u.deleted_at IS NULL"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function activate(int $id): bool
    {
        return static::update($id, [
            'status' => 'active',
            'phone_verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function touchLastLogin(int $id): bool
    {
        return static::update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public static function findByUsername(string $username): ?array
    {
        return static::findBy('username', $username);
    }

    public static function updateUsername(int $id, string $username): bool
    {
        return static::update($id, ['username' => $username]);
    }

    public static function countByRole(): array
    {
        $stmt = Database::connection()->query(
            "SELECT r.slug, COUNT(*) AS total
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL
             GROUP BY r.slug"
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['slug']] = (int) $row['total'];
        }

        return $result;
    }

    public static function adminListing(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['u.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = 'r.slug = :role';
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'u.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['keyword'])) {
            $where[] = '(u.full_name LIKE :keyword_name OR u.email LIKE :keyword_email)';
            $params['keyword_name'] = '%' . $filters['keyword'] . '%';
            $params['keyword_email'] = '%' . $filters['keyword'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT u.id, u.full_name, u.email, u.phone, u.status, u.created_at, r.slug AS role_slug, r.label AS role_label
                FROM users u JOIN roles r ON r.id = u.role_id
                WHERE {$whereSql}
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function setStatus(int $id, string $status): bool
    {
        return static::update($id, ['status' => $status]);
    }

    public static function generateUniqueUsername(string $fullName): string
    {
        $base = strtolower(trim($fullName));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base);
        $base = trim($base, '-');
        $base = substr($base, 0, 40);

        if ($base === '') {
            $base = 'student';
        }

        $username = $base;
        $suffix = 1;

        while (static::findByUsername($username) !== null) {
            $suffix++;
            $username = $base . '-' . $suffix;
        }

        return $username;
    }
}
