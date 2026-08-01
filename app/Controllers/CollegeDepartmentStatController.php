<?php

namespace App\Controllers;

use App\Models\CollegeDepartmentStat;

class CollegeDepartmentStatController extends CollegeSubResourceController
{
    protected function modelClass(): string
    {
        return CollegeDepartmentStat::class;
    }

    protected function fields(): array
    {
        return ['department_name', 'academic_year', 'total_students', 'students_placed', 'average_package', 'highest_package'];
    }

    protected function rules(): array
    {
        return [
            'department_name' => 'required|min:2|max:150',
            'total_students' => 'numeric',
            'students_placed' => 'numeric',
            'average_package' => 'numeric',
            'highest_package' => 'numeric',
        ];
    }
}
