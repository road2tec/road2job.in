<?php

namespace App\Controllers;

use App\Models\StudentProfile;
use App\Services\ResumeCompiler;
use App\Services\ResumeScorer;
use Core\Controller;
use Core\Request;
use Core\Session;

class ResumeController extends Controller
{
    protected const TEMPLATES = ['ats', 'professional', 'creative'];

    public function preview(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $data = ResumeCompiler::compile($userId);
        $savedTemplate = $data['profile']['resume_template'] ?? 'ats';

        $requestedTemplate = (string) $request->input('template', '');
        $activeTemplate = in_array($requestedTemplate, self::TEMPLATES, true) ? $requestedTemplate : $savedTemplate;

        $this->view('resume/templates/' . $activeTemplate, array_merge($data, [
            'isOwnerView' => true,
            'activeTemplate' => $activeTemplate,
            'savedTemplate' => $savedTemplate,
            'templates' => self::TEMPLATES,
            'score' => ResumeScorer::score($data),
        ]), 'print');
    }

    public function updateTemplate(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $template = (string) $request->input('template', '');

        if (!in_array($template, self::TEMPLATES, true)) {
            Session::flash('error', 'Invalid template.');
            $this->redirect('/dashboard/resume');
            return;
        }

        StudentProfile::saveForUser($userId, ['resume_template' => $template]);

        Session::flash('success', 'Default resume template updated.');
        $this->redirect('/dashboard/resume?template=' . $template);
    }
}
