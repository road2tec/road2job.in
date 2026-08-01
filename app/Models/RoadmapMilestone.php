<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class RoadmapMilestone extends Model
{
    protected static string $table = 'roadmap_milestones';

    public static function forRoadmap(int $roadmapId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT m.*, c.title AS course_title
             FROM roadmap_milestones m
             LEFT JOIN institute_courses c ON c.id = m.course_id
             WHERE m.roadmap_id = :roadmap_id
             ORDER BY m.order_index ASC"
        );
        $stmt->execute(['roadmap_id' => $roadmapId]);

        return $stmt->fetchAll();
    }

    public static function nextOrderIndex(int $roadmapId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COALESCE(MAX(order_index), -1) + 1 FROM roadmap_milestones WHERE roadmap_id = :roadmap_id"
        );
        $stmt->execute(['roadmap_id' => $roadmapId]);

        return (int) $stmt->fetchColumn();
    }
}
