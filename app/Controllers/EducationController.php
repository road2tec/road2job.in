<?php

namespace App\Controllers;

use App\Models\StudentEducation;

class EducationController extends StudentSubResourceController
{
    protected function modelClass(): string
    {
        return StudentEducation::class;
    }

    protected function fields(): array
    {
        return ['degree', 'institution_name', 'field_of_study', 'start_date', 'end_date', 'grade', 'description'];
    }

    protected function rules(): array
    {
        return [
            'degree' => 'required|max:150',
            'institution_name' => 'required|max:150',
            'field_of_study' => 'max:150',
            'start_date' => 'required|date',
            'end_date' => 'date',
            'grade' => 'max:50',
        ];
    }
}
