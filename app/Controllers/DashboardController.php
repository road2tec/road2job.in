<?php

namespace App\Controllers;

use App\Models\College;
use App\Models\CollegeCampusDrive;
use App\Models\CollegeDriveRegistration;
use App\Models\Company;
use App\Models\Institute;
use App\Models\InstituteCourse;
use App\Models\InstitutePlacement;
use App\Models\InstituteUpdate;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\MentorProfile;
use App\Models\MentorshipRequest;
use App\Models\StudentSkill;
use App\Services\CareerSuggester;
use App\Services\InstituteRankingScorer;
use App\Services\JobMatchScorer;
use App\Services\NotificationService;
use App\Services\PlacementReadinessScorer;
use App\Services\ResumeCompiler;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $user = Session::get('_user');
        $roles = config('roles');
        $layout = $roles[$user['role_slug']]['layout'] ?? 'student';

        $notifications = (new NotificationService())->recentFor($user['id'], 5);

        $extra = [];

        if ($user['role_slug'] === 'employer') {
            $company = Company::findByUserId((int) $user['id']);
            $extra['company'] = $company;
            $extra['publishedJobCount'] = $company !== null ? JobPosting::countPublishedForCompany((int) $company['id']) : 0;
        }

        if ($user['role_slug'] === 'institute') {
            $institute = Institute::findByUserId((int) $user['id']);
            $extra['institute'] = $institute;

            if ($institute !== null) {
                $instituteId = (int) $institute['id'];
                $courses = InstituteCourse::forInstitute($instituteId);
                $extra['publishedCourseCount'] = count(array_filter($courses, fn ($c) => $c['status'] === 'published'));

                // Real ranking/completion stats - InstituteRankingScorer
                // already recomputes on every profile/placement/update
                // write, so rank_score here is always current, not stale.
                $extra['profileCompletionPercent'] = (int) $institute['profile_completion_percent'];
                $extra['rankPosition'] = Institute::rankPosition($instituteId, (float) $institute['rank_score']);
                $extra['activeInstituteCount'] = Institute::countActive();
                $extra['totalPlacementCount'] = InstitutePlacement::countActiveForInstitute($instituteId);
                $extra['recentPlacementCount'] = InstitutePlacement::countRecentForInstitute($instituteId, 7);
                $extra['totalUpdateCount'] = count(InstituteUpdate::activeForInstitute($instituteId));
                $extra['recentUpdateCount'] = InstituteUpdate::countRecentForInstitute($instituteId, 7);
                $extra['portfolioUrl'] = url('/institutes/' . $instituteId);
            } else {
                $extra['publishedCourseCount'] = 0;
                $extra['profileCompletionPercent'] = 0;
                $extra['rankPosition'] = null;
                $extra['activeInstituteCount'] = 0;
                $extra['totalPlacementCount'] = 0;
                $extra['recentPlacementCount'] = 0;
                $extra['totalUpdateCount'] = 0;
                $extra['recentUpdateCount'] = 0;
                $extra['portfolioUrl'] = null;
            }
        }

        if ($user['role_slug'] === 'college') {
            $college = College::findByUserId((int) $user['id']);
            $extra['college'] = $college;

            if ($college !== null) {
                $drives = CollegeCampusDrive::forCollege((int) $college['id']);
                $extra['publishedDriveCount'] = count(array_filter($drives, fn ($d) => $d['status'] === 'published'));
                $pendingRegistrations = array_filter(CollegeDriveRegistration::forCollege((int) $college['id']), fn ($r) => $r['status'] === 'pending');
                $extra['pendingRegistrationCount'] = count($pendingRegistrations);
            } else {
                $extra['publishedDriveCount'] = 0;
                $extra['pendingRegistrationCount'] = 0;
            }
        }

        if ($user['role_slug'] === 'student') {
            $userId = (int) $user['id'];
            $resumeData = ResumeCompiler::compile($userId);
            $applicationCount = count(JobApplication::forStudent($userId));
            $studentSkillNames = array_map(fn ($row) => $row['skill_name'], StudentSkill::forUser($userId));

            $extra['readiness'] = PlacementReadinessScorer::score($resumeData, $applicationCount);
            $extra['careerSuggestions'] = CareerSuggester::suggest($studentSkillNames);
            $extra['recommendedJobs'] = JobMatchScorer::rankJobsForStudent(JobPosting::search([]), $studentSkillNames, 3);

            foreach (Institute::staleIds() as $staleId) {
                InstituteRankingScorer::recompute((int) $staleId);
            }
            $extra['topInstitutes'] = Institute::topRanked(3);
            $extra['trendingInstitutes'] = Institute::trending(3);
        }

        if ($user['role_slug'] === 'mentor') {
            $mentor = MentorProfile::findByUserId((int) $user['id']);
            $extra['mentor'] = $mentor;

            if ($mentor !== null) {
                $pendingRequests = array_filter(MentorshipRequest::forMentor((int) $mentor['id']), fn ($r) => $r['status'] === 'pending');
                $extra['pendingMentorshipCount'] = count($pendingRequests);
            } else {
                $extra['pendingMentorshipCount'] = 0;
            }
        }

        $this->view("dashboard/{$layout}/home", array_merge([
            'user' => $user,
            'notifications' => $notifications,
        ], $extra), $layout);
    }

    public function comingSoon(Request $request, string $key): void
    {
        $pages = config('coming_soon');

        if (!isset($pages[$key])) {
            Response::abort(404);
            return;
        }

        $user = Session::get('_user');
        $roles = config('roles');
        $layout = $roles[$user['role_slug']]['layout'] ?? 'student';

        $this->view('dashboard/coming_soon', [
            'user' => $user,
            'page' => $pages[$key],
            'showDemoQr' => $key === 'payments',
        ], $layout);
    }
}
