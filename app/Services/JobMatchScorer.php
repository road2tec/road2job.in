<?php

namespace App\Services;

class JobMatchScorer
{
    protected static function allDictionarySkills(): array
    {
        $skills = [];

        foreach (config('skills_dictionary')['categories'] as $category) {
            $skills = array_merge($skills, $category['skills']);
        }

        return array_values(array_unique($skills));
    }

    public static function extractSkillsFromText(string $text): array
    {
        $matched = [];

        foreach (self::allDictionarySkills() as $skill) {
            $pattern = '/\b' . preg_quote($skill, '/') . '\b/i';

            if (preg_match($pattern, $text)) {
                $matched[] = $skill;
            }
        }

        return $matched;
    }

    public static function scoreForStudent(array $job, array $studentSkillNames): array
    {
        $text = ($job['title'] ?? '') . ' ' . ($job['description'] ?? '') . ' ' . ($job['requirements'] ?? '');
        $required = self::extractSkillsFromText($text);

        if (empty($required)) {
            return ['percent' => null, 'matched' => [], 'missing' => [], 'required' => []];
        }

        $studentSkillsLower = array_map('strtolower', $studentSkillNames);

        $matched = [];
        $missing = [];

        foreach ($required as $skill) {
            if (in_array(strtolower($skill), $studentSkillsLower, true)) {
                $matched[] = $skill;
            } else {
                $missing[] = $skill;
            }
        }

        return [
            'percent' => (int) round((count($matched) / count($required)) * 100),
            'matched' => $matched,
            'missing' => $missing,
            'required' => $required,
        ];
    }

    public static function rankJobsForStudent(array $jobs, array $studentSkillNames, int $limit = 5): array
    {
        $scored = [];

        foreach ($jobs as $job) {
            $score = self::scoreForStudent($job, $studentSkillNames);

            if ($score['percent'] === null) {
                continue;
            }

            $job['matchPercent'] = $score['percent'];
            $scored[] = $job;
        }

        usort($scored, fn ($a, $b) => $b['matchPercent'] <=> $a['matchPercent']);

        return array_slice($scored, 0, $limit);
    }
}
