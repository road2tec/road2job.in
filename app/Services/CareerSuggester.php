<?php

namespace App\Services;

class CareerSuggester
{
    public static function suggest(array $studentSkillNames, int $limit = 3): array
    {
        $studentSkillsLower = array_map('strtolower', $studentSkillNames);
        $suggestions = [];

        foreach (config('skills_dictionary')['categories'] as $categoryName => $category) {
            $matchedSkills = [];

            foreach ($category['skills'] as $skill) {
                if (in_array(strtolower($skill), $studentSkillsLower, true)) {
                    $matchedSkills[] = $skill;
                }
            }

            if (empty($matchedSkills)) {
                continue;
            }

            $suggestions[] = [
                'category' => $categoryName,
                'roles' => $category['roles'],
                'matchedSkills' => $matchedSkills,
                'matchCount' => count($matchedSkills),
            ];
        }

        usort($suggestions, fn ($a, $b) => $b['matchCount'] <=> $a['matchCount']);

        return array_slice($suggestions, 0, $limit);
    }
}
