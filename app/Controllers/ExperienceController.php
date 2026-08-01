<?php

namespace App\Controllers;

use App\Models\StudentExperience;
use Core\Request;

class ExperienceController extends StudentSubResourceController
{
    protected function modelClass(): string
    {
        return StudentExperience::class;
    }

    protected function fields(): array
    {
        return ['job_title', 'company_name', 'location', 'employment_type', 'start_date', 'end_date', 'currently_working', 'description'];
    }

    protected function rules(): array
    {
        return [
            'job_title' => 'required|max:150',
            'company_name' => 'required|max:150',
            'location' => 'max:150',
            'employment_type' => 'required|in:internship,full_time,part_time,freelance,contract,training',
            'start_date' => 'required|date',
            'end_date' => 'date',
        ];
    }

    protected function prepareData(Request $request, array $data): array
    {
        $data['currently_working'] = $request->input('currently_working') ? 1 : 0;

        if ($data['currently_working'] === 1) {
            $data['end_date'] = null;
        }

        // Not in fields()/rules() (it's a free-form chip list, not a
        // single validated string) - read and join separately.
        $skillsUsed = (array) $request->input('skills_used', []);
        $skillsUsed = array_values(array_filter(array_map('trim', $skillsUsed), fn ($v) => $v !== ''));
        $data['skills_used'] = !empty($skillsUsed) ? implode(', ', $skillsUsed) : null;

        return $data;
    }
}
