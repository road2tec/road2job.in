<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstituteUpdate extends Model
{
    protected static string $table = 'institute_updates';

    /**
     * Unfiltered - owner's own dashboard CRUD list.
     */
    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_updates WHERE institute_id = :institute_id ORDER BY created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }

    /**
     * status='active' only - the public portfolio feed and
     * InstituteRankingScorer both read through this, never the raw table.
     */
    public static function activeForInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_updates WHERE institute_id = :institute_id AND status = 'active' ORDER BY created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }

    public static function setStatus(int $id, string $status): void
    {
        static::update($id, ['status' => $status]);
    }

    public static function countRecentForInstitute(int $instituteId, int $days = 7): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM institute_updates WHERE institute_id = :institute_id AND status = 'active' AND created_at >= :since"
        );
        $stmt->execute([
            'institute_id' => $instituteId,
            'since' => date('Y-m-d H:i:s', strtotime("-{$days} days")),
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Global chronological feed across every active institute's active
     * updates - powers the student-facing "Institute Updates" page. No
     * per-student personalization/follow concept exists, so this is simply
     * the most recent real posts, newest first.
     */
    public static function recentAcrossInstitutes(int $limit = 30): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT u.*, i.name AS institute_name, i.logo_path AS institute_logo_path, i.city AS institute_city
             FROM institute_updates u
             JOIN institutes i ON i.id = u.institute_id
             WHERE u.status = 'active' AND i.status = 'active'
             ORDER BY u.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
