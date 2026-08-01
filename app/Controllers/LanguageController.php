<?php

namespace App\Controllers;

use App\Models\StudentLanguage;

class LanguageController extends StudentSubResourceController
{
    protected function modelClass(): string
    {
        return StudentLanguage::class;
    }

    protected function fields(): array
    {
        return ['language_name', 'proficiency'];
    }

    protected function rules(): array
    {
        return [
            'language_name' => 'required|max:100',
            'proficiency' => 'required|in:basic,conversational,fluent,native',
        ];
    }
}
