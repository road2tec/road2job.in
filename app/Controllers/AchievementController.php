<?php

namespace App\Controllers;

use App\Models\StudentAchievement;

class AchievementController extends StudentSubResourceController
{
    protected function modelClass(): string
    {
        return StudentAchievement::class;
    }

    protected function fields(): array
    {
        return ['title', 'description', 'achieved_on'];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|max:150',
            'achieved_on' => 'required|date',
        ];
    }
}
