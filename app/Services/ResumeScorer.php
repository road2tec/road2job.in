<?php

namespace App\Services;

class ResumeScorer
{
    public static function score(array $compiledData): array
    {
        $profile = $compiledData['profile'] ?? [];
        $account = $compiledData['account'] ?? [];

        $items = [
            ['label' => 'Headline', 'done' => !empty($profile['headline'])],
            ['label' => 'Career objective', 'done' => !empty($profile['career_objective'])],
            ['label' => 'Education', 'done' => !empty($compiledData['education'])],
            ['label' => 'Skills', 'done' => !empty($compiledData['skills'])],
            ['label' => 'A project or work experience', 'done' => !empty($compiledData['projects']) || !empty($compiledData['experience'])],
            ['label' => 'A certificate or achievement', 'done' => !empty($compiledData['certificates']) || !empty($compiledData['achievements'])],
            ['label' => 'Profile photo', 'done' => !empty($profile['avatar_path'])],
            ['label' => 'Phone number', 'done' => !empty($account['phone'])],
        ];

        $done = count(array_filter($items, fn ($item) => $item['done']));
        $percent = (int) round(($done / count($items)) * 100);

        return [
            'percent' => $percent,
            'items' => $items,
        ];
    }
}
