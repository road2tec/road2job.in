<?php

namespace App\Services;

/**
 * "Profile Strength" for the institute dashboard - mirrors ProfileScorer's
 * shape exactly (stateless score() over plain arrays, no DB access here).
 * Its 'percent' output also feeds InstituteRankingScorer's completeness
 * term directly, so the dashboard widget and the ranking formula can never
 * drift apart - one source of truth for "how complete is this profile."
 */
class InstituteProfileScorer
{
    public static function score(array $institute, array $placements, array $updates): array
    {
        $hasSocialLink = !empty($institute['linkedin_url']) || !empty($institute['instagram_url'])
            || !empty($institute['youtube_url']) || !empty($institute['facebook_url']) || !empty($institute['twitter_url']);

        $items = [
            ['label' => 'Institute name', 'done' => !empty($institute['name'])],
            ['label' => 'Logo', 'done' => !empty($institute['logo_path'])],
            ['label' => 'Cover image', 'done' => !empty($institute['cover_path'])],
            ['label' => 'About / description', 'done' => !empty($institute['description'])],
            ['label' => 'Location', 'done' => !empty($institute['city']) || !empty($institute['location'])],
            ['label' => 'Contact number', 'done' => !empty($institute['phone'])],
            ['label' => 'Official email', 'done' => !empty($institute['official_email'])],
            ['label' => 'Website', 'done' => !empty($institute['website'])],
            ['label' => 'Specializations', 'done' => !empty($institute['specializations'])],
            ['label' => 'Facilities', 'done' => !empty($institute['facilities'])],
            ['label' => 'Social profiles', 'done' => $hasSocialLink],
            ['label' => 'Placements', 'done' => !empty($placements)],
            ['label' => 'Institute updates', 'done' => !empty($updates)],
        ];

        $done = count(array_filter($items, fn ($item) => $item['done']));
        $percent = (int) round(($done / count($items)) * 100);

        $nextAction = null;
        foreach ($items as $item) {
            if (!$item['done']) {
                $nextAction = self::actionFor($item['label']);
                break;
            }
        }

        return [
            'percent' => $percent,
            'items' => $items,
            'nextAction' => $nextAction,
        ];
    }

    protected static function actionFor(string $label): string
    {
        return match ($label) {
            'Institute name' => 'Add your institute name to improve your profile.',
            'Logo' => 'Upload a logo to improve your profile.',
            'Cover image' => 'Upload a cover image to improve your profile.',
            'About / description' => 'Add a description to improve your profile.',
            'Location' => 'Add your city to improve your profile.',
            'Contact number' => 'Add a contact number to improve your profile.',
            'Official email' => 'Add an official email to improve your profile.',
            'Website' => 'Add your website to improve your profile.',
            'Specializations' => 'Add your specializations to improve your profile.',
            'Facilities' => 'Add your facilities to improve your profile.',
            'Social profiles' => 'Add a social profile link to improve your profile.',
            'Placements' => 'Add a placement record to improve your profile.',
            'Institute updates' => 'Post an institute update to improve your profile.',
            default => 'Keep building your profile.',
        };
    }
}
