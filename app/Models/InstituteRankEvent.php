<?php

namespace App\Models;

use Core\Model;
use Core\Database;

/**
 * Append-only activity ledger, never edited after insert - gives
 * InstituteRankingScorer's "trending" query an honest short-term-momentum
 * signal that survives a placement/update later being edited or deleted
 * (the event itself already happened).
 */
class InstituteRankEvent extends Model
{
    protected static string $table = 'institute_rank_events';
    protected static bool $timestamps = false;

    public static function record(int $instituteId, string $eventType): void
    {
        static::insert([
            'institute_id' => $instituteId,
            'event_type' => $eventType,
            'event_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function countRecentForInstitute(int $instituteId, int $days): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM institute_rank_events WHERE institute_id = :institute_id AND event_at >= :since"
        );
        $stmt->execute([
            'institute_id' => $instituteId,
            'since' => date('Y-m-d H:i:s', strtotime("-{$days} days")),
        ]);

        return (int) $stmt->fetchColumn();
    }
}
