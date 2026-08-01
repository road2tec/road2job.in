<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class LearningRoadmap extends Model
{
    protected static string $table = 'learning_roadmaps';

    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM learning_roadmaps WHERE institute_id = :institute_id ORDER BY created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }

    public static function publicListing(?int $page = null, int $perPage = 24): array
    {
        if ($page === null) {
            $stmt = Database::connection()->prepare(
                "SELECT r.*, i.name AS institute_name
                 FROM learning_roadmaps r
                 JOIN institutes i ON i.id = r.institute_id
                 ORDER BY r.created_at DESC"
            );
            $stmt->execute();

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare('SELECT COUNT(*) FROM learning_roadmaps');
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT r.*, i.name AS institute_name
             FROM learning_roadmaps r
             JOIN institutes i ON i.id = r.institute_id
             ORDER BY r.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function findWithInstitute(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, i.name AS institute_name
             FROM learning_roadmaps r
             JOIN institutes i ON i.id = r.institute_id
             WHERE r.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
