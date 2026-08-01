<?php

namespace App\Services;

/**
 * "Profile Strength" for the profile builder page itself - distinct from
 * ResumeScorer, which scores compiled resume data on the resume page.
 * Computed fresh from real section counts every page load, never stored/
 * cached, so it can never go stale relative to what's actually saved.
 */
class ProfileScorer
{
    public static function score(array $account, ?array $profile, array $education, array $skills, array $projects, array $experience, array $certificates, array $achievements): array
    {
        $items = [
            ['label' => 'Basic information', 'done' => !empty($profile['headline']) && !empty($account['phone'])],
            ['label' => 'Education', 'done' => !empty($education)],
            ['label' => 'Skills', 'done' => !empty($skills)],
            ['label' => 'Experience', 'done' => !empty($experience)],
            ['label' => 'Projects', 'done' => !empty($projects)],
            ['label' => 'Certifications', 'done' => !empty($certificates) || !empty($achievements)],
            ['label' => 'Social profiles', 'done' => !empty($profile['linkedin_url']) || !empty($profile['github_url'])],
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
            'Basic information' => 'Add a headline and phone number to improve your profile.',
            'Education' => 'Add your education to improve your profile.',
            'Skills' => 'Add a few skills to improve your profile.',
            'Experience' => 'Add work experience or an internship to improve your profile.',
            'Projects' => 'Add 1 project to improve your profile.',
            'Certifications' => 'Add a certification or achievement to improve your profile.',
            'Social profiles' => 'Add your LinkedIn or GitHub link to improve your profile.',
            default => 'Keep building your profile.',
        };
    }
}
