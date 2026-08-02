<?php

namespace App\Controllers;

use App\Models\BlogPost;
use App\Models\College;
use App\Models\CollegeAlumnus;
use App\Models\CollegeCampusDrive;
use App\Models\CollegeDepartmentStat;
use App\Models\CollegeDriveRegistration;
use App\Models\Company;
use App\Models\CommunityPost;
use App\Models\Event;
use App\Models\Institute;
use App\Models\InstituteCertificate;
use App\Models\InstituteCourse;
use App\Models\InstituteFaculty;
use App\Models\InstituteGallery;
use App\Models\InstitutePlacement;
use App\Models\InstituteReview;
use App\Models\InstituteUpdate;
use App\Services\InstituteRankingScorer;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\LearningRoadmap;
use App\Models\MentorProfile;
use App\Models\ResearchItem;
use App\Models\SavedJob;
use App\Models\StudentSkill;
use App\Services\JobMatchScorer;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class PageController extends Controller
{
    public function home(Request $request): void
    {
        $this->view('pages/home', [
            'meta' => [
                'title' => 'Road2Job - AI Powered Career, Placement & Recruitment Ecosystem',
                'description' => 'Road2Job connects students, employers, training institutes and colleges on one career and recruitment platform.',
            ],
            'latestJobs' => JobPosting::publishedListing(6),
        ], 'marketing');
    }

    public function about(Request $request): void
    {
        $this->view('pages/about', [
            'meta' => [
                'title' => 'About Us - Road2Job',
                'description' => 'Why we built Road2Job and what we are building towards.',
            ],
        ], 'marketing');
    }

    public function faq(Request $request): void
    {
        $this->view('pages/faq', [
            'meta' => [
                'title' => 'FAQs - Road2Job',
                'description' => 'Answers to common questions about registering, verifying your account, and using Road2Job.',
            ],
        ], 'marketing');
    }

    public function privacy(Request $request): void
    {
        $this->view('pages/privacy', [
            'meta' => [
                'title' => 'Privacy Policy - Road2Job',
                'description' => 'How Road2Job collects, uses and protects your data.',
            ],
        ], 'marketing');
    }

    public function terms(Request $request): void
    {
        $this->view('pages/terms', [
            'meta' => [
                'title' => 'Terms of Service - Road2Job',
                'description' => 'The terms that govern your use of Road2Job.',
            ],
        ], 'marketing');
    }

    public function careers(Request $request): void
    {
        $this->view('pages/careers', [
            'meta' => [
                'title' => 'Careers at Road2Job',
                'description' => 'Join the team building Road2Job.',
            ],
        ], 'marketing');
    }

    public function jobs(Request $request): void
    {
        $filters = $this->jobFilters($request);
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = JobPosting::search($filters, null, $page, $perPage);

        $this->view('pages/jobs_public', [
            'jobs' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters,
            'lockType' => false,
            'savedJobIds' => $this->savedJobIdsForCurrentStudent(),
            'pageTitle' => 'Jobs',
            'pageIntro' => 'Browse open roles from employers on Road2Job.',
            'meta' => [
                'title' => 'Jobs - Road2Job',
                'description' => 'Browse open roles from employers on Road2Job.',
            ],
        ], 'marketing');
    }

    public function internships(Request $request): void
    {
        $filters = $this->jobFilters($request);
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = JobPosting::search($filters, 'internship', $page, $perPage);

        $this->view('pages/jobs_public', [
            'jobs' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters,
            'lockType' => true,
            'savedJobIds' => $this->savedJobIdsForCurrentStudent(),
            'pageTitle' => 'Internships',
            'pageIntro' => 'Verified internships posted by employers on Road2Job.',
            'meta' => [
                'title' => 'Internships - Road2Job',
                'description' => 'Browse internships posted by employers on Road2Job.',
            ],
        ], 'marketing');
    }

    /**
     * Empty array for guests/non-students - the save button itself is only
     * ever rendered for logged-in students (see jobs_public.php), this just
     * avoids one query per card for whoever IS a logged-in student.
     */
    protected function savedJobIdsForCurrentStudent(): array
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null || $sessionUser['role_slug'] !== 'student') {
            return [];
        }

        return SavedJob::savedIdsForStudent((int) $sessionUser['id']);
    }

    protected function jobFilters(Request $request): array
    {
        return [
            'type' => (string) $request->input('type', ''),
            'location' => (string) $request->input('location', ''),
            'experience_level' => (string) $request->input('experience_level', ''),
            'is_remote' => (string) $request->input('is_remote', ''),
            'keyword' => (string) $request->input('keyword', ''),
            'min_salary' => (string) $request->input('min_salary', ''),
            'company' => (string) $request->input('company', ''),
            'skills' => (string) $request->input('skills', ''),
        ];
    }

    public function jobShow(Request $request, string $id): void
    {
        $job = JobPosting::findPublished((int) $id);

        if ($job === null) {
            Response::abort(404);
            return;
        }

        $sessionUser = Session::get('_user');
        $isStudent = $sessionUser !== null && $sessionUser['role_slug'] === 'student';
        $hasApplied = false;
        $isSaved = false;
        $matchScore = null;

        if ($isStudent) {
            $studentId = (int) $sessionUser['id'];
            $hasApplied = JobApplication::hasApplied((int) $id, $studentId);
            $isSaved = SavedJob::isSaved($studentId, (int) $id);

            $studentSkillNames = array_map(fn ($row) => $row['skill_name'], StudentSkill::forUser($studentId));
            $matchScore = JobMatchScorer::scoreForStudent($job, $studentSkillNames);
        }

        $this->view('pages/job_show', [
            'job' => $job,
            'isStudent' => $isStudent,
            'hasApplied' => $hasApplied,
            'isSaved' => $isSaved,
            'matchScore' => $matchScore,
            'meta' => [
                'title' => $job['title'] . ' at ' . $job['company_name'] . ' - Road2Job',
                'description' => 'Job opening: ' . $job['title'] . ' at ' . $job['company_name'] . '.',
            ],
        ], 'marketing');
    }

    public function institutes(Request $request): void
    {
        foreach (Institute::staleIds() as $staleId) {
            InstituteRankingScorer::recompute((int) $staleId);
        }

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $filters = [
            'city' => trim((string) $request->input('city', '')),
            'institute_type' => trim((string) $request->input('institute_type', '')),
            'training_mode' => trim((string) $request->input('training_mode', '')),
            'specialization' => trim((string) $request->input('specialization', '')),
            'verified_only' => (bool) $request->input('verified_only', false),
        ];
        $result = Institute::publicListing($page, $perPage, $filters);

        $this->view('pages/institutes_public', [
            'institutes' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters,
            'meta' => [
                'title' => 'Training Institutes - Road2Job',
                'description' => 'Browse training institutes, their courses and placement records on Road2Job.',
            ],
        ], 'marketing');
    }

    public function instituteShow(Request $request, string $id): void
    {
        $institute = Institute::find((int) $id);

        if ($institute === null || empty($institute['name']) || ($institute['status'] ?? 'active') !== 'active') {
            Response::abort(404);
            return;
        }

        $instituteId = (int) $institute['id'];
        $sessionUser = Session::get('_user');
        $isStudent = $sessionUser !== null && $sessionUser['role_slug'] === 'student';

        $isOwnInstitute = false;
        if ($isStudent) {
            $ownInstitute = Institute::findByUserId((int) $sessionUser['id']);
            $isOwnInstitute = $ownInstitute !== null && (int) $ownInstitute['id'] === $instituteId;
        }

        // Deactivated institutes are hidden from the public entirely - the
        // owner previewing their own still sees it, mirroring the student
        // portfolio's private-preview pattern.
        if ($institute['status'] !== 'active' && !$isOwnInstitute) {
            Response::abort(404);
            return;
        }

        if (!$isOwnInstitute) {
            Institute::update($instituteId, ['profile_view_count' => (int) $institute['profile_view_count'] + 1]);
        }

        $alreadyReviewed = $isStudent && InstituteReview::findByInstituteAndUser($instituteId, (int) $sessionUser['id']) !== null;

        $courses = InstituteCourse::publishedForInstitute($instituteId);

        $placementFilters = [
            'company' => trim((string) $request->input('company', '')),
            'year' => trim((string) $request->input('year', '')),
        ];

        $this->view('pages/institute_show', [
            'institute' => $institute,
            'courses' => $courses,
            'faculty' => InstituteFaculty::forInstitute($instituteId),
            'gallery' => InstituteGallery::forInstitute($instituteId),
            'certificates' => InstituteCertificate::forInstitute($instituteId),
            'placements' => InstitutePlacement::activeForInstitute($instituteId, $placementFilters),
            'placementFilters' => $placementFilters,
            'updates' => InstituteUpdate::activeForInstitute($instituteId),
            'reviews' => InstituteReview::forInstitute($instituteId),
            'averageRating' => InstituteReview::averageRating($instituteId),
            'isStudent' => $isStudent,
            'alreadyReviewed' => $alreadyReviewed,
            'isOwnInstitute' => $isOwnInstitute,
            'meta' => [
                'title' => $institute['name'] . ' - Road2Job',
                'description' => 'Courses, faculty and placement record for ' . $institute['name'] . ' on Road2Job.',
                'image' => !empty($institute['cover_path']) ? url($institute['cover_path']) : (!empty($institute['logo_path']) ? url($institute['logo_path']) : null),
            ],
            'extraStyles' => ['portfolio.css'],
            'extraScripts' => ['portfolio.js'],
        ], 'marketing');
    }

    public function colleges(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = College::publicListing($page, $perPage);

        $this->view('pages/colleges_public', [
            'colleges' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'meta' => [
                'title' => 'Colleges - Road2Job',
                'description' => 'Browse college placement cells, campus drives and placement records on Road2Job.',
            ],
        ], 'marketing');
    }

    public function collegeShow(Request $request, string $id): void
    {
        $college = College::find((int) $id);

        if ($college === null || empty($college['name'])) {
            Response::abort(404);
            return;
        }

        $collegeId = (int) $college['id'];
        $sessionUser = Session::get('_user');
        $isStudent = $sessionUser !== null && $sessionUser['role_slug'] === 'student';

        $drives = CollegeCampusDrive::publishedForCollege($collegeId);

        if ($isStudent) {
            foreach ($drives as &$drive) {
                $drive['already_registered'] = CollegeDriveRegistration::hasActiveRequest((int) $drive['id'], (int) $sessionUser['id']);
            }
            unset($drive);
        }

        $this->view('pages/college_show', [
            'college' => $college,
            'drives' => $drives,
            'departmentStats' => CollegeDepartmentStat::forCollege($collegeId),
            'alumni' => CollegeAlumnus::forCollege($collegeId),
            'isStudent' => $isStudent,
            'meta' => [
                'title' => $college['name'] . ' - Road2Job',
                'description' => 'Campus drives, placement stats and alumni for ' . $college['name'] . ' on Road2Job.',
            ],
        ], 'marketing');
    }

    public function companies(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = Company::publicListing($page, $perPage);
        $companies = $result['items'];

        foreach ($companies as &$company) {
            $company['published_job_count'] = JobPosting::countPublishedForCompany((int) $company['id']);
        }
        unset($company);

        $this->view('pages/companies_public', [
            'companies' => $companies,
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'meta' => [
                'title' => 'Companies - Road2Job',
                'description' => 'Browse employer profiles and their open roles on Road2Job.',
            ],
        ], 'marketing');
    }

    public function companyShow(Request $request, string $id): void
    {
        $company = Company::find((int) $id);

        if ($company === null || empty($company['name'])) {
            Response::abort(404);
            return;
        }

        $publishedJobs = array_values(array_filter(
            JobPosting::forCompany((int) $company['id']),
            fn ($job) => $job['status'] === 'published'
        ));

        $this->view('pages/company_show', [
            'company' => $company,
            'jobs' => $publishedJobs,
            'meta' => [
                'title' => $company['name'] . ' - Road2Job',
                'description' => 'Open roles and company profile for ' . $company['name'] . ' on Road2Job.',
            ],
        ], 'marketing');
    }

    public function researchHub(Request $request): void
    {
        $type = (string) $request->input('type', '');
        $validTypes = ['research-paper', 'project', 'publication', 'conference-paper', 'patent'];
        $type = in_array($type, $validTypes, true) ? $type : null;

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = ResearchItem::publicListing($type, 50, $page, $perPage);

        $this->view('pages/research_hub', [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'activeType' => $type,
            'types' => $validTypes,
            'meta' => [
                'title' => 'Research Hub - Road2Job',
                'description' => 'Research papers, projects, publications, conference papers and patents shared by students on Road2Job.',
            ],
        ], 'marketing');
    }

    public function researchItemShow(Request $request, string $id): void
    {
        $item = ResearchItem::findPublicWithAuthor((int) $id);

        if ($item === null) {
            Response::abort(404);
            return;
        }

        $this->view('pages/research_item_show', [
            'item' => $item,
            'meta' => [
                'title' => $item['title'] . ' - Road2Job Research Hub',
                'description' => 'Research from ' . $item['author_name'] . ' on Road2Job.',
            ],
        ], 'marketing');
    }

    public function robots(Request $request): void
    {
        $disallow = ['/dashboard', '/admin', '/account', '/login', '/register', '/verify-otp', '/verify-email', '/forgot-password', '/reset-password', '/uploads/'];

        $lines = ['User-agent: *', 'Allow: /'];
        foreach ($disallow as $path) {
            $lines[] = 'Disallow: ' . $path;
        }
        $lines[] = '';
        $lines[] = 'Sitemap: ' . url('/sitemap.xml');

        header('Content-Type: text/plain');
        echo implode("\n", $lines) . "\n";
        exit;
    }

    public function sitemap(Request $request): void
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => url('/jobs'), 'priority' => '0.9'],
            ['loc' => url('/internships'), 'priority' => '0.8'],
            ['loc' => url('/companies'), 'priority' => '0.7'],
            ['loc' => url('/institutes'), 'priority' => '0.7'],
            ['loc' => url('/colleges'), 'priority' => '0.7'],
            ['loc' => url('/community'), 'priority' => '0.6'],
            ['loc' => url('/mentors'), 'priority' => '0.6'],
            ['loc' => url('/research-hub'), 'priority' => '0.6'],
            ['loc' => url('/roadmaps'), 'priority' => '0.6'],
            ['loc' => url('/events'), 'priority' => '0.6'],
            ['loc' => url('/blog'), 'priority' => '0.6'],
            ['loc' => url('/about'), 'priority' => '0.4'],
            ['loc' => url('/faq'), 'priority' => '0.3'],
            ['loc' => url('/privacy-policy'), 'priority' => '0.2'],
            ['loc' => url('/terms-of-service'), 'priority' => '0.2'],
            ['loc' => url('/careers'), 'priority' => '0.4'],
        ];

        $entitySets = [
            ['rows' => JobPosting::publishedListing(1000), 'path' => '/jobs/', 'priority' => '0.8'],
            ['rows' => Company::publicListing(), 'path' => '/companies/', 'priority' => '0.6'],
            ['rows' => Institute::publicListing(), 'path' => '/institutes/', 'priority' => '0.6'],
            ['rows' => College::publicListing(), 'path' => '/colleges/', 'priority' => '0.6'],
            ['rows' => CommunityPost::forCategory(null, 1000), 'path' => '/community/', 'priority' => '0.5'],
            ['rows' => MentorProfile::publicListing(), 'path' => '/mentors/', 'priority' => '0.5'],
            ['rows' => ResearchItem::publicListing(null, 1000), 'path' => '/research-hub/', 'priority' => '0.5'],
            ['rows' => LearningRoadmap::publicListing(), 'path' => '/roadmaps/', 'priority' => '0.5'],
            ['rows' => Event::publicListing(), 'path' => '/events/', 'priority' => '0.5'],
            ['rows' => BlogPost::publishedListing(), 'path' => '/blog/', 'priority' => '0.6'],
        ];

        foreach ($entitySets as $set) {
            foreach ($set['rows'] as $row) {
                $urls[] = [
                    'loc' => url($set['path'] . $row['id']),
                    'priority' => $set['priority'],
                    'lastmod' => !empty($row['updated_at']) ? date('Y-m-d', strtotime($row['updated_at'])) : null,
                ];
            }
        }

        header('Content-Type: application/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            echo '  <url>' . "\n";
            echo '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . '</loc>' . "\n";
            if (!empty($url['lastmod'])) {
                echo '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            }
            echo '    <priority>' . $url['priority'] . '</priority>' . "\n";
            echo '  </url>' . "\n";
        }
        echo '</urlset>' . "\n";
        exit;
    }

    public function comingSoon(Request $request, string $key): void
    {
        $pages = config('coming_soon');

        if (!isset($pages[$key])) {
            Response::abort(404);
            return;
        }

        $page = $pages[$key];

        $this->view('pages/coming_soon', [
            'page' => $page,
            'meta' => [
                'title' => $page['title'] . ' (Coming Soon) - Road2Job',
                'description' => $page['description'],
            ],
        ], 'marketing');
    }
}
