<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Institute extends Model
{
    protected static string $table = 'institutes';

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

    /**
     * 1-based rank among active institutes by rank_score - a single cheap
     * indexed COUNT, safe to run on a dashboard page load (not a public
     * listing page, so no staleness-sweep concern here).
     */
    public static function rankPosition(int $instituteId, float $rankScore): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM institutes WHERE status = 'active' AND rank_score > :score"
        );
        $stmt->execute(['score' => $rankScore]);

        return (int) $stmt->fetchColumn() + 1;
    }

    public static function countActive(): int
    {
        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM institutes WHERE status = 'active' AND name IS NOT NULL");
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * $filters: city, institute_type, training_mode (LIKE against the
     * comma-joined column), specialization (LIKE against specializations).
     * Ranked read - ORDER BY rank_score DESC is a plain indexed scan
     * (idx_institutes_status_rank), no live computation happens here;
     * InstituteRankingScorer::recompute() is what keeps rank_score fresh,
     * called at write time plus the lazy staleId() sweep (see below).
     */
    public static function publicListing(?int $page = null, int $perPage = 24, array $filters = []): array
    {
        [$where, $params] = self::buildPublicFilterClause($filters);

        if ($page === null) {
            $stmt = Database::connection()->prepare(
                "SELECT * FROM institutes WHERE {$where} ORDER BY rank_score DESC, created_at DESC"
            );
            $stmt->execute($params);

            return $stmt->fetchAll();
        }

        $countStmt = Database::connection()->prepare("SELECT COUNT(*) FROM institutes WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT * FROM institutes WHERE {$where} ORDER BY rank_score DESC, created_at DESC LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    protected static function buildPublicFilterClause(array $filters): array
    {
        $where = ["name IS NOT NULL", "status = 'active'"];
        $params = [];

        if (!empty($filters['city'])) {
            // Two distinct placeholders for the same value - PDO's native
            // (non-emulated) prepares reject a named parameter used twice
            // in one query (SQLSTATE[HY093]: Invalid parameter number).
            $where[] = '(city LIKE :city OR location LIKE :city_location)';
            $params['city'] = '%' . $filters['city'] . '%';
            $params['city_location'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['institute_type'])) {
            $where[] = 'institute_type = :institute_type';
            $params['institute_type'] = $filters['institute_type'];
        }

        if (!empty($filters['training_mode'])) {
            $where[] = 'training_modes LIKE :training_mode';
            $params['training_mode'] = '%' . $filters['training_mode'] . '%';
        }

        if (!empty($filters['specialization'])) {
            $where[] = 'specializations LIKE :specialization';
            $params['specialization'] = '%' . $filters['specialization'] . '%';
        }

        if (!empty($filters['verified_only'])) {
            $where[] = "verification_status = 'verified'";
        }

        return [implode(' AND ', $where), $params];
    }

    public static function topRanked(int $limit = 3): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institutes WHERE name IS NOT NULL AND status = 'active' ORDER BY rank_score DESC, created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Short-term momentum, not just the absolute score - institutes with
     * the most recent activity (institute_rank_events, last 14 days) sort
     * first, rank_score only breaks ties among equally-active institutes.
     */
    public static function trending(int $limit = 3): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT i.*, COUNT(re.id) AS recent_activity
             FROM institutes i
             LEFT JOIN institute_rank_events re ON re.institute_id = i.id AND re.event_at >= :since
             WHERE i.name IS NOT NULL AND i.status = 'active'
             GROUP BY i.id
             HAVING recent_activity > 0
             ORDER BY recent_activity DESC, i.rank_score DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':since', date('Y-m-d H:i:s', strtotime('-14 days')));
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function forComparison(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare(
            "SELECT * FROM institutes WHERE id IN ({$placeholders}) AND status = 'active' AND name IS NOT NULL"
        );
        $stmt->execute($ids);

        return $stmt->fetchAll();
    }

    /**
     * Bounded batch (default 20) of institutes whose rank_score hasn't been
     * recomputed in 24h+ - the caller (a controller, not this model) feeds
     * each id through InstituteRankingScorer::recompute() right before
     * rendering a listing, so an institute that's gone quiet still decays
     * correctly without needing a cron job.
     */
    public static function staleIds(int $limit = 20): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT id FROM institutes WHERE status = 'active' AND (rank_score_updated_at IS NULL OR rank_score_updated_at < :cutoff) LIMIT :limit"
        );
        $stmt->bindValue(':cutoff', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return array_column($stmt->fetchAll(), 'id');
    }

    public static function adminListing(?string $keyword = null, int $page = 1, int $perPage = 20): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($keyword)) {
            $where = '(i.name LIKE :keyword_name OR u.full_name LIKE :keyword_owner OR u.email LIKE :keyword_email)';
            $params['keyword_name'] = '%' . $keyword . '%';
            $params['keyword_owner'] = '%' . $keyword . '%';
            $params['keyword_email'] = '%' . $keyword . '%';
        }

        $countStmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM institutes i JOIN users u ON u.id = i.user_id WHERE {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            "SELECT i.*, u.full_name AS owner_name, u.email AS owner_email, u.status AS owner_status
             FROM institutes i JOIN users u ON u.id = i.user_id
             WHERE {$where}
             ORDER BY i.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $page - 1) * $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }
}
