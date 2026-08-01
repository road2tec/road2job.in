<?php

namespace App\Services;

class PlacementReadinessScorer
{
    public static function score(array $compiledResumeData, int $applicationCount): array
    {
        $completeness = ResumeScorer::score($compiledResumeData)['percent'];

        $skillCount = count($compiledResumeData['skills'] ?? []);
        $skillFactor = min($skillCount / 5, 1) * 100;

        $hasExperienceOrProject = !empty($compiledResumeData['experience']) || !empty($compiledResumeData['projects']);
        $experienceFactor = $hasExperienceOrProject ? 100 : 0;

        $hasCertOrAchievement = !empty($compiledResumeData['certificates']) || !empty($compiledResumeData['achievements']);
        $certFactor = $hasCertOrAchievement ? 100 : 0;

        $applicationFactor = $applicationCount >= 1 ? 100 : 0;

        $percent = (int) round(
            $completeness * 0.4
            + $skillFactor * 0.2
            + $experienceFactor * 0.2
            + $certFactor * 0.1
            + $applicationFactor * 0.1
        );

        $suggestions = [];

        if ($completeness < 100) {
            $suggestions[] = 'Fill in the remaining sections of your resume (see the checklist on your Resume page).';
        }

        if ($skillCount < 5) {
            $suggestions[] = 'Add more skills to your profile - aim for at least 5 to stand out.';
        }

        if (!$hasExperienceOrProject) {
            $suggestions[] = 'Add a project or work experience to strengthen your profile.';
        }

        if (!$hasCertOrAchievement) {
            $suggestions[] = 'Add a certificate or achievement to showcase your accomplishments.';
        }

        if ($applicationCount === 0) {
            $suggestions[] = 'Start applying to jobs to build momentum - browse open roles on the Jobs page.';
        }

        return [
            'percent' => $percent,
            'suggestions' => $suggestions,
        ];
    }
}
