<?php

namespace App\Controllers;

use App\Models\PortfolioView;
use App\Models\ResearchItem;
use App\Models\StudentProfile;
use App\Models\StudentProject;
use App\Models\User;
use App\Services\ResumeCompiler;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class PortfolioController extends Controller
{
    /**
     * Canonical portfolio sections, in default display order. The
     * Portfolio Manager lets a student hide/reorder these (stored as a
     * comma list of visible keys in student_profiles.portfolio_section_order)
     * - keys are the single source of truth shared by the manager UI and
     * the public template's !empty()-gated section loop.
     */
    public const SECTIONS = [
        'about' => 'About',
        'skills' => 'Skills',
        'projects' => 'Projects',
        'experience' => 'Experience',
        'education' => 'Education',
        'certificates' => 'Certificates',
        'achievements' => 'Achievements',
        'languages' => 'Languages',
        'research' => 'Research & Publications',
        'hire_me' => 'Hire Me',
        'contact' => 'Contact',
    ];

    public function show(Request $request, string $username): void
    {
        $visible = $this->findVisibleProfile($username);

        if ($visible === null) {
            Response::abort(404);
            return;
        }

        if (!$visible['isPublic'] && !$visible['isOwner']) {
            $this->view('pages/portfolio_private', [
                'meta' => [
                    'title' => 'Private Portfolio - Road2Job',
                    'description' => 'This portfolio is private.',
                ],
            ], 'marketing');
            return;
        }

        $user = $visible['user'];
        $profile = $visible['profile'];

        if (!$visible['isOwner']) {
            PortfolioView::record((int) $user['id'], $request->ip());
        }

        $data = ResumeCompiler::compile((int) $user['id']);
        $researchItems = ResearchItem::forUser((int) $user['id']);

        // Featured projects always lead, newest-first otherwise (stable
        // sort - PHP's usort() is guaranteed stable since 8.0).
        $projects = $data['projects'];
        usort($projects, fn ($a, $b) => ($b['is_featured'] ?? 0) <=> ($a['is_featured'] ?? 0));
        $data['projects'] = $projects;

        $hasSection = [
            'about' => !empty($profile['career_objective']),
            'skills' => !empty($data['skills']),
            'projects' => !empty($data['projects']),
            'experience' => !empty($data['experience']),
            'education' => !empty($data['education']),
            'certificates' => !empty($data['certificates']),
            'achievements' => !empty($data['achievements']),
            'languages' => !empty($data['languages']),
            'research' => !empty($researchItems),
            'hire_me' => !empty($profile['interested_roles']) || !empty($profile['preferred_locations'])
                || !empty($profile['work_type']) || !empty($profile['availability']) || !empty($profile['domains_of_interest']),
            'contact' => true,
        ];

        $order = $this->resolveSectionOrder($profile['portfolio_section_order'] ?? null);
        $visibleSections = array_values(array_filter($order, fn ($key) => !empty($hasSection[$key])));

        $portfolioUrl = url('/u/' . $user['username']);
        $avatarUrl = !empty($profile['avatar_path']) ? url($profile['avatar_path']) : null;

        $this->view('pages/portfolio_show', array_merge($data, [
            'resumeUrl' => url('/u/' . $user['username'] . '/resume'),
            'researchItems' => $researchItems,
            'isPublicView' => $visible['isPublic'],
            'isOwner' => $visible['isOwner'],
            'sectionLabels' => self::SECTIONS,
            'visibleSections' => $visibleSections,
            'skillGroups' => $this->groupSkillsByCategory($data['skills']),
            'portfolioUrl' => $portfolioUrl,
            'shareLinks' => [
                'whatsapp' => 'https://wa.me/?text=' . rawurlencode($user['full_name'] . "'s Road2Job portfolio: " . $portfolioUrl),
                'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($portfolioUrl),
            ],
            'meta' => [
                'title' => $user['full_name'] . ' - Road2Job',
                'description' => trim(($profile['headline'] ?? '') . ' - ' . $user['full_name'] . ' on Road2Job'),
                'image' => $avatarUrl,
            ],
            'extraStyles' => ['portfolio.css'],
            'extraScripts' => ['portfolio.js'],
        ]), 'marketing');
    }

    /**
     * Buckets a student's skills into the Pass-1 catalog's categories
     * (app/data/skills_catalog.php) for the portfolio's grouped display -
     * a custom skill typed outside the catalog falls into "Other" rather
     * than being dropped.
     */
    protected function groupSkillsByCategory(array $studentSkills): array
    {
        // app/data/skills_catalog.php's own top-level code (it's require'd,
        // not called, so it shares this scope) uses $categories/$flat/
        // $suggestions internally - the local var names here are chosen to
        // never collide with those (a previous version used $skills and got
        // silently clobbered by the catalog file's internal `foreach (...
        // as $skills)` loop).
        $catalogData = require base_path('app/data/skills_catalog.php');
        $catalogCategories = $catalogData['categories'] ?? [];

        $lookup = [];
        foreach ($catalogCategories as $categoryName => $names) {
            foreach ($names as $name) {
                $lookup[strtolower($name)] = $categoryName;
            }
        }

        $grouped = [];
        foreach ($studentSkills as $skill) {
            $category = $lookup[strtolower(trim((string) $skill['skill_name']))] ?? 'Other';
            $grouped[$category][] = $skill;
        }

        $ordered = [];
        foreach (array_keys($catalogCategories) as $categoryName) {
            if (isset($grouped[$categoryName])) {
                $ordered[$categoryName] = $grouped[$categoryName];
            }
        }
        if (isset($grouped['Other'])) {
            $ordered['Other'] = $grouped['Other'];
        }

        return $ordered;
    }

    public function resume(Request $request, string $username): void
    {
        $visible = $this->findVisibleProfile($username);

        if ($visible === null) {
            Response::abort(404);
            return;
        }

        if (!$visible['isPublic'] && !$visible['isOwner']) {
            $this->view('pages/portfolio_private', [
                'meta' => [
                    'title' => 'Private Portfolio - Road2Job',
                    'description' => 'This portfolio is private.',
                ],
            ], 'marketing');
            return;
        }

        $userId = (int) $visible['user']['id'];
        $data = ResumeCompiler::compile($userId);
        $template = $data['profile']['resume_template'] ?? 'professional';

        $this->view('resume/templates/' . $template, array_merge($data, [
            'isOwnerView' => false,
        ]), 'print');
    }

    public function manage(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $account = User::find($userId);

        if (empty($account['username'])) {
            $username = User::generateUniqueUsername($account['full_name']);
            User::updateUsername($userId, $username);
            $account['username'] = $username;
        }

        $profile = StudentProfile::findByUserId($userId);

        $this->view('dashboard/student/portfolio', [
            'user' => $sessionUser,
            'account' => $account,
            'profile' => $profile,
            'portfolioUrl' => url('/u/' . $account['username']),
            'totalViews' => PortfolioView::countTotal($userId),
            'uniqueVisitors' => PortfolioView::countUniqueVisitors($userId),
            'sections' => self::SECTIONS,
            'sectionOrder' => $this->resolveSectionOrder($profile['portfolio_section_order'] ?? null),
            'projects' => StudentProject::forUser($userId),
        ], 'student');
    }

    public function updateManager(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        // Only "modern" ships fully designed this pass - other values in the
        // <select> are disabled client-side, this is the server-side backstop.
        $allowedThemes = ['modern'];
        $requestedTheme = (string) $request->input('portfolio_theme', 'modern');
        $theme = in_array($requestedTheme, $allowedThemes, true) ? $requestedTheme : 'modern';

        $requestedOrder = array_values(array_filter(array_map('trim', (array) $request->input('section_order', []))));
        $validKeys = array_keys(self::SECTIONS);
        $sectionOrder = array_values(array_intersect($requestedOrder, $validKeys));
        // array_intersect() re-keys but preserves the submitted order since
        // it iterates $requestedOrder - re-index defensively either way.

        $visibility = $request->input('profile_visibility') === 'public' ? 'public' : 'private';

        StudentProfile::saveForUser($userId, [
            'portfolio_theme' => $theme,
            'portfolio_section_order' => !empty($sectionOrder) ? implode(',', $sectionOrder) : null,
            'profile_visibility' => $visibility,
        ]);

        $featuredIds = array_map('intval', (array) $request->input('featured_projects', []));
        StudentProject::setFeaturedForUser($userId, $featuredIds);

        Session::flash('success', 'Portfolio settings updated.');
        $this->redirect('/dashboard/portfolio');
    }

    protected function resolveSectionOrder(?string $stored): array
    {
        $default = array_keys(self::SECTIONS);

        if (empty($stored)) {
            return $default;
        }

        $keys = array_values(array_filter(array_map('trim', explode(',', $stored))));
        $keys = array_values(array_intersect($keys, $default));

        return !empty($keys) ? $keys : $default;
    }

    protected function findVisibleProfile(string $username): ?array
    {
        $user = User::findByUsername($username);

        if ($user === null) {
            return null;
        }

        $profile = StudentProfile::findByUserId((int) $user['id']);
        $sessionUser = Session::get('_user');
        $isOwner = $sessionUser !== null && (int) $sessionUser['id'] === (int) $user['id'];
        $visibility = $profile['profile_visibility'] ?? 'private';

        return [
            'user' => $user,
            'profile' => $profile,
            'isOwner' => $isOwner,
            'isPublic' => $visibility === 'public',
        ];
    }
}
