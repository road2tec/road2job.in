<?php

namespace App\Services;

use App\Models\Institute;
use App\Models\InstitutePlacement;
use App\Models\InstituteRankEvent;
use App\Models\InstituteUpdate;

/**
 * Dynamic institute ranking - ONE reusable service, called at write time
 * (profile save, placement/update add-edit-delete, admin moderation) so
 * public listing pages just do a plain indexed `ORDER BY rank_score DESC`
 * instead of computing anything live. See Institute::publicListing()/
 * topRanked()/trending() for the lazy 24h staleness sweep that keeps
 * inactive institutes' scores from going stale without needing a cron job.
 *
 * Formula (all four sub-scores normalized 0-100 before weighting):
 *   rank_score = (0.35 * PlacementQuality + 0.25 * UpdatesActivity
 *                 + 0.20 * ProfileCompleteness + 0.20 * Consistency)
 *                * SpamPenaltyMultiplier
 *
 * Deliberately NOT "more posts = higher rank": both PlacementQuality and
 * UpdatesActivity use a saturating 1-exp(-x/k) curve so each additional
 * entry contributes less than the last, duplicate entries are excluded
 * from the sum entirely (not just down-weighted), and a posting burst
 * applies a flat penalty multiplier - see recompute() for the exact math.
 */
class InstituteRankingScorer
{
    protected const PLACEMENT_RECENCY_MONTHS = 24;
    protected const UPDATE_RECENCY_DAYS = 90;
    protected const CONSISTENCY_MONTHS = 6;
    protected const BURST_WINDOW_HOURS = 24;
    protected const BURST_THRESHOLD = 5;
    protected const BURST_PENALTY = 0.85;
    protected const SPAM_FLOOR = 0.5;

    public static function recompute(int $instituteId): void
    {
        $institute = Institute::find($instituteId);

        if ($institute === null) {
            return;
        }

        $placements = InstitutePlacement::activeForInstitute($instituteId);
        $updates = InstituteUpdate::activeForInstitute($instituteId);

        $dedupedPlacements = self::dedupePlacements($placements);
        $dedupedUpdates = self::dedupeUpdates($updates);

        $placementScore = self::placementQualityScore($dedupedPlacements);
        $updatesScore = self::updatesActivityScore($dedupedUpdates);
        $consistencyScore = self::consistencyScore($placements, $updates);
        $completeness = InstituteProfileScorer::score($institute, $placements, $updates);
        $completenessScore = (float) $completeness['percent'];

        $spamMultiplier = self::spamPenaltyMultiplier($updates);

        $rankScore = (
            0.35 * $placementScore
            + 0.25 * $updatesScore
            + 0.20 * $completenessScore
            + 0.20 * $consistencyScore
        ) * $spamMultiplier;

        Institute::update($instituteId, [
            'rank_score' => round($rankScore, 3),
            'rank_score_updated_at' => date('Y-m-d H:i:s'),
            'profile_completion_percent' => $completeness['percent'],
        ]);
    }

    /**
     * Called by controllers right after a placement/update/profile write so
     * the activity ledger (trending's data source) reflects it immediately.
     */
    public static function recordEvent(int $instituteId, string $eventType): void
    {
        InstituteRankEvent::record($instituteId, $eventType);
        self::recompute($instituteId);
    }

    protected static function placementQualityScore(array $placements): float
    {
        $sum = 0.0;
        $now = time();

        foreach ($placements as $placement) {
            $dateString = $placement['placement_date'] ?? (!empty($placement['placement_year']) ? $placement['placement_year'] . '-01-01' : null);

            if ($dateString === null) {
                continue;
            }

            $ageMonths = max(0, ($now - strtotime($dateString)) / (30.44 * 86400));
            $recencyWeight = max(0, 1 - ($ageMonths / self::PLACEMENT_RECENCY_MONTHS));

            if ($recencyWeight <= 0) {
                continue;
            }

            $qualityWeight = 1.0
                + (!empty($placement['student_photo_path']) ? 0.1 : 0)
                + (!empty($placement['job_role']) ? 0.1 : 0)
                + (!empty($placement['description']) ? 0.1 : 0);

            $sum += $recencyWeight * $qualityWeight;
        }

        return 100 * (1 - exp(-$sum / 8));
    }

    protected static function updatesActivityScore(array $updates): float
    {
        $sum = 0.0;
        $now = time();
        $substantiveCategories = ['placement_news', 'achievement'];

        foreach ($updates as $update) {
            $ageDays = max(0, ($now - strtotime($update['created_at'])) / 86400);
            $recencyWeight = max(0, 1 - ($ageDays / self::UPDATE_RECENCY_DAYS));

            if ($recencyWeight <= 0) {
                continue;
            }

            $categoryWeight = in_array($update['category'], $substantiveCategories, true) ? 1.2 : 1.0;
            $sum += $recencyWeight * $categoryWeight;
        }

        return 100 * (1 - exp(-$sum / 5));
    }

    /**
     * Rewards activity spread across the trailing 6 months rather than a
     * single burst - uses BOTH placements and updates (not deduped, since a
     * duplicate posted in month 3 still shows the institute was active that
     * month, just not that it was substantively productive that month).
     */
    protected static function consistencyScore(array $placements, array $updates): float
    {
        $activeBuckets = [];
        $now = time();

        foreach (array_merge($placements, $updates) as $row) {
            $dateString = $row['placement_date']
                ?? (!empty($row['placement_year']) ? $row['placement_year'] . '-01-01' : null)
                ?? ($row['created_at'] ?? null);

            if ($dateString === null) {
                continue;
            }

            $timestamp = strtotime($dateString);
            $monthsAgo = (int) floor(($now - $timestamp) / (30.44 * 86400));

            if ($monthsAgo >= 0 && $monthsAgo < self::CONSISTENCY_MONTHS) {
                $activeBuckets[$monthsAgo] = true;
            }
        }

        return 100 * (count($activeBuckets) / self::CONSISTENCY_MONTHS);
    }

    /**
     * Exact duplicates (same normalized student+company+year) beyond the
     * first occurrence are dropped entirely from the ranking sum - not
     * down-weighted - so re-posting the same placement can never inflate
     * the score.
     */
    protected static function dedupePlacements(array $placements): array
    {
        $seen = [];
        $result = [];

        foreach ($placements as $placement) {
            $key = strtolower(trim((string) $placement['student_name'])) . '|'
                . strtolower(trim((string) $placement['company_name'])) . '|'
                . ($placement['placement_year'] ?? '');

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $placement;
        }

        return $result;
    }

    protected static function dedupeUpdates(array $updates): array
    {
        $seen = [];
        $result = [];

        foreach ($updates as $update) {
            $key = $update['content_hash'] ?? null;

            if ($key !== null) {
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
            }

            $result[] = $update;
        }

        return $result;
    }

    /**
     * Starts at 1.0, reduced (never increased) by a flat penalty for each
     * qualifying burst window in the trailing 30 days - clamped to a floor
     * so spam hurts but never fully zeroes an otherwise-legitimate profile.
     */
    protected static function spamPenaltyMultiplier(array $updates): float
    {
        $cutoff = time() - (30 * 86400);
        $timestamps = [];

        foreach ($updates as $update) {
            $ts = strtotime($update['created_at']);
            if ($ts >= $cutoff) {
                $timestamps[] = $ts;
            }
        }

        sort($timestamps);
        $windowSeconds = self::BURST_WINDOW_HOURS * 3600;
        $burstDetected = false;

        foreach ($timestamps as $i => $start) {
            $count = 0;
            foreach ($timestamps as $candidate) {
                if ($candidate >= $start && $candidate < $start + $windowSeconds) {
                    $count++;
                }
            }
            if ($count > self::BURST_THRESHOLD) {
                $burstDetected = true;
                break;
            }
        }

        $multiplier = $burstDetected ? self::BURST_PENALTY : 1.0;

        return max(self::SPAM_FLOOR, $multiplier);
    }
}
