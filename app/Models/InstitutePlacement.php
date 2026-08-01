<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstitutePlacement extends Model
{
    protected static string $table = 'institute_placements';

    /**
     * Unfiltered - used by the owner's own dashboard CRUD table, where a
     * deactivated (admin-moderated) placement should still be visible so
     * the institute can see why it's not counting toward their ranking.
     */
    public static function forInstitute(int $instituteId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institute_placements WHERE institute_id = :institute_id ORDER BY placement_year DESC, created_at DESC"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return $stmt->fetchAll();
    }

    /**
     * status='active' only - used by the public portfolio page and by
     * InstituteRankingScorer (an admin-deactivated placement must stop
     * counting immediately). Optional search/filter for the public
     * placement-wall search box.
     */
    public static function activeForInstitute(int $instituteId, array $filters = []): array
    {
        $where = ['institute_id = :institute_id', "status = 'active'"];
        $params = ['institute_id' => $instituteId];

        if (!empty($filters['company'])) {
            $where[] = 'company_name LIKE :company';
            $params['company'] = '%' . $filters['company'] . '%';
        }

        if (!empty($filters['year'])) {
            $where[] = 'placement_year = :year';
            $params['year'] = (int) $filters['year'];
        }

        $sql = 'SELECT * FROM institute_placements WHERE ' . implode(' AND ', $where) . ' ORDER BY placement_year DESC, created_at DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function countActiveForInstitute(int $instituteId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM institute_placements WHERE institute_id = :institute_id AND status = 'active'"
        );
        $stmt->execute(['institute_id' => $instituteId]);

        return (int) $stmt->fetchColumn();
    }

    public static function countRecentForInstitute(int $instituteId, int $days = 7): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM institute_placements WHERE institute_id = :institute_id AND status = 'active' AND created_at >= :since"
        );
        $stmt->execute([
            'institute_id' => $instituteId,
            'since' => date('Y-m-d H:i:s', strtotime("-{$days} days")),
        ]);

        return (int) $stmt->fetchColumn();
    }

    public static function setStatus(int $id, string $status): void
    {
        static::update($id, ['status' => $status]);
    }

    /**
     * Null unless at least 3 disclosed package_amount values exist -
     * averaging 1-2 self-reported numbers into a headline figure would
     * misleadingly imply a statistic that doesn't really exist yet. Used
     * only on the comparison page, which must never fabricate/imply more
     * precision than the underlying data supports.
     */
    public static function averagePackage(int $instituteId): ?float
    {
        $stmt = Database::connection()->prepare(
            "SELECT package_amount FROM institute_placements WHERE institute_id = :institute_id AND status = 'active' AND package_amount IS NOT NULL"
        );
        $stmt->execute(['institute_id' => $instituteId]);
        $amounts = array_column($stmt->fetchAll(), 'package_amount');

        if (count($amounts) < 3) {
            return null;
        }

        return array_sum($amounts) / count($amounts);
    }
}
