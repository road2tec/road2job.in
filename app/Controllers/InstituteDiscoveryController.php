<?php

namespace App\Controllers;

use App\Models\Institute;
use App\Models\InstituteCourse;
use App\Models\InstitutePlacement;
use App\Models\InstituteReview;
use App\Services\InstituteRankingScorer;
use Core\Controller;
use Core\Request;

/**
 * Public institute discovery beyond the plain directory listing
 * (PageController::institutes()) - "compare" specifically. Kept separate
 * from PageController since comparison is a distinct, self-contained
 * feature (no pagination, no CRUD), not because institutes() itself needed
 * splitting.
 */
class InstituteDiscoveryController extends Controller
{
    public function compare(Request $request): void
    {
        // Accepts either ids=1,2,3 (a typed/shared link) or ids[]=1&ids[]=2
        // (the discovery page's compare-checkbox form submits via GET).
        $rawIds = $request->input('ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
        $ids = array_filter(array_map('trim', $ids));
        $ids = array_slice($ids, 0, 4);

        // Same lazy staleness rule as the listing pages (>24h since last
        // recompute) - a bounded set of at most 4 ids, so checking each
        // directly here is cheap and avoids an unconditional recompute on
        // every comparison page view.
        foreach ($ids as $id) {
            $institute = Institute::find((int) $id);
            $isStale = $institute !== null
                && (empty($institute['rank_score_updated_at']) || strtotime($institute['rank_score_updated_at']) < strtotime('-24 hours'));

            if ($isStale) {
                InstituteRankingScorer::recompute((int) $id);
            }
        }

        $institutes = Institute::forComparison($ids);

        // Attach factual, never-fabricated comparison stats per institute -
        // computed here (not in the view) so the view stays a pure
        // presentation layer with a simple ?? 'Not provided' fallback.
        foreach ($institutes as &$institute) {
            $instituteId = (int) $institute['id'];
            $institute['course_count'] = count(InstituteCourse::publishedForInstitute($instituteId));
            $institute['placement_count'] = InstitutePlacement::countActiveForInstitute($instituteId);
            $institute['average_package'] = InstitutePlacement::averagePackage($instituteId);
            $institute['average_rating'] = InstituteReview::averageRating($instituteId);
        }
        unset($institute);

        $this->view('pages/institutes_compare', [
            'institutes' => $institutes,
            'meta' => [
                'title' => 'Compare Institutes - Road2Job',
                'description' => 'Compare training institutes side by side using factual profile, course and placement information.',
            ],
        ], 'marketing');
    }
}
