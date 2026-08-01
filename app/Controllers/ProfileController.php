<?php

namespace App\Controllers;

use App\Models\StudentAchievement;
use App\Models\StudentCertificate;
use App\Models\StudentEducation;
use App\Models\StudentExperience;
use App\Models\StudentLanguage;
use App\Models\StudentProfile;
use App\Models\StudentProject;
use App\Models\StudentSkill;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\ProfileScorer;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class ProfileController extends Controller
{
    public function show(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $user = User::find($userId);
        $profile = StudentProfile::findByUserId($userId);

        $education = StudentEducation::forUser($userId);
        $skills = StudentSkill::forUser($userId);
        $projects = StudentProject::forUser($userId);
        $experience = StudentExperience::forUser($userId);
        $certificates = StudentCertificate::forUser($userId);
        $achievements = StudentAchievement::forUser($userId);
        $languages = StudentLanguage::forUser($userId);

        $this->view('dashboard/student/profile', [
            'user' => $sessionUser,
            'account' => $user,
            'profile' => $profile,
            'education' => $education,
            'skills' => $skills,
            'projects' => $projects,
            'experience' => $experience,
            'certificates' => $certificates,
            'achievements' => $achievements,
            'languages' => $languages,
            'timeline' => $this->buildTimeline($education, $experience, $certificates, $achievements),
            'profileScore' => ProfileScorer::score($user ?? [], $profile, $education, $skills, $projects, $experience, $certificates, $achievements),
            'skillsCatalog' => require base_path('app/data/skills_catalog.php'),
            'degreesCatalog' => require base_path('app/data/degrees.php'),
            'branchesCatalog' => require base_path('app/data/branches.php'),
            'jobTitlesCatalog' => require base_path('app/data/job_titles.php'),
            'projectTypesCatalog' => require base_path('app/data/project_types.php'),
            'organizationsCatalog' => require base_path('app/data/common_organizations.php'),
            'languagesCatalog' => require base_path('app/data/common_languages.php'),
            'locationsCatalog' => require base_path('app/data/locations.php'),
            'domainsCatalog' => require base_path('app/data/domains.php'),
        ], 'student');
    }

    public function update(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $data = $request->only([
            'full_name', 'headline', 'bio', 'date_of_birth', 'gender',
            'address_line', 'city', 'state', 'country', 'pincode', 'career_objective',
            'linkedin_url', 'github_url', 'leetcode_url', 'hackerrank_url', 'codechef_url',
            'behance_url', 'dribbble_url', 'youtube_url', 'website_url', 'availability',
        ]);

        $validator = Validator::make($data);
        $validator->validate([
            'full_name' => 'required|min:2|max:150',
            'headline' => 'max:150',
            'date_of_birth' => 'date',
            'gender' => 'in:male,female,other,prefer_not_to_say',
            'pincode' => 'max:10',
            'linkedin_url' => 'url',
            'github_url' => 'url',
            'leetcode_url' => 'url',
            'hackerrank_url' => 'url',
            'codechef_url' => 'url',
            'behance_url' => 'url',
            'dribbble_url' => 'url',
            'youtube_url' => 'url',
            'website_url' => 'url',
            'availability' => 'in:actively_looking,open_to_opportunities,not_looking',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/profile');
            return;
        }

        User::update($userId, ['full_name' => $data['full_name']]);
        unset($data['full_name']);

        // Nullable DATE/ENUM columns reject empty strings - normalize blanks to NULL.
        foreach (['date_of_birth', 'gender', 'availability'] as $nullableField) {
            if (($data[$nullableField] ?? '') === '') {
                $data[$nullableField] = null;
            }
        }

        // Chip multi-selects / checkbox groups - not single validated strings,
        // read and comma-join separately (same pattern as Experience::skills_used).
        foreach (['interested_roles' => 'interested_roles', 'preferred_locations' => 'preferred_locations', 'domains_of_interest' => 'domains_of_interest'] as $inputName => $column) {
            $values = (array) $request->input($inputName, []);
            $values = array_values(array_filter(array_map('trim', $values), fn ($v) => $v !== ''));
            $data[$column] = !empty($values) ? implode(', ', $values) : null;
        }

        $workType = (array) $request->input('work_type', []);
        $workType = array_values(array_filter(array_map('trim', $workType), fn ($v) => $v !== ''));
        $data['work_type'] = !empty($workType) ? implode(', ', $workType) : null;

        $avatarFile = $request->file('avatar');
        if ($avatarFile !== null && ($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $data['avatar_path'] = (new FileUploadService())->upload($avatarFile, 'avatar');
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('/dashboard/profile');
                return;
            }
        }

        StudentProfile::saveForUser($userId, $data);

        Session::put('_user', array_merge($sessionUser, ['full_name' => $request->input('full_name')]));

        Session::flash('success', 'Profile updated.');
        $this->redirect('/dashboard/profile');
    }

    protected function buildTimeline(array $education, array $experience, array $certificates, array $achievements): array
    {
        $items = [];

        foreach ($education as $row) {
            $items[] = [
                'type' => 'Education',
                'icon' => 'bi-mortarboard',
                'title' => $row['degree'] . ' - ' . $row['institution_name'],
                'sort_date' => $row['start_date'],
                'date' => display_date($row['start_date'], 'Date unknown'),
            ];
        }

        foreach ($experience as $row) {
            $items[] = [
                'type' => 'Experience',
                'icon' => 'bi-briefcase',
                'title' => $row['job_title'] . ' at ' . $row['company_name'],
                'sort_date' => $row['start_date'],
                'date' => display_date($row['start_date'], 'Date unknown'),
            ];
        }

        foreach ($certificates as $row) {
            $items[] = [
                'type' => 'Certificate',
                'icon' => 'bi-award',
                'title' => $row['title'] . ' - ' . $row['issuing_organization'],
                'sort_date' => $row['issue_date'],
                'date' => display_date($row['issue_date'], 'Date unknown'),
            ];
        }

        foreach ($achievements as $row) {
            $items[] = [
                'type' => 'Achievement',
                'icon' => 'bi-trophy',
                'title' => $row['title'],
                'sort_date' => $row['achieved_on'],
                'date' => display_date($row['achieved_on'], 'Date unknown'),
            ];
        }

        usort($items, fn ($a, $b) => strcmp($b['sort_date'], $a['sort_date']));

        return $items;
    }
}
