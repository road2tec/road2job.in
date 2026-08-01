<?php

namespace App\Controllers;

use App\Models\AssessmentAttempt;
use App\Models\College;
use App\Models\CollegeCampusDrive;
use App\Models\CollegeDriveRegistration;
use App\Models\Company;
use App\Models\Institute;
use App\Models\InstituteCourse;
use App\Models\InstituteEnrollmentRequest;
use App\Models\InterviewSession;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Session;

class AnalyticsController extends Controller
{
    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $roleSlug = $sessionUser['role_slug'];
        $roles = config('roles');
        $layout = $roles[$roleSlug]['layout'] ?? 'student';

        $data = match ($roleSlug) {
            'student' => $this->studentData((int) $sessionUser['id']),
            'employer' => $this->employerData((int) $sessionUser['id']),
            'institute' => $this->instituteData((int) $sessionUser['id']),
            'college' => $this->collegeData((int) $sessionUser['id']),
            'admin', 'super_admin' => $this->adminData(),
            default => null,
        };

        if ($data === null) {
            Session::flash('error', "Analytics isn't available for your role yet.");
            $this->redirect('/dashboard');
            return;
        }

        $view = in_array($roleSlug, ['admin', 'super_admin'], true) ? 'admin' : $roleSlug;

        $this->view('dashboard/analytics/' . $view, array_merge([
            'user' => $sessionUser,
        ], $data), $layout);
    }

    protected function studentData(int $studentId): array
    {
        $applications = JobApplication::forStudent($studentId);

        return [
            'applicationFunnel' => $this->countByStatus($applications),
            'assessmentScores' => AssessmentAttempt::bestPerCategory($studentId),
        ];
    }

    protected function employerData(int $userId): array
    {
        $company = Company::findByUserId($userId);

        if ($company === null) {
            return [
                'jobFunnel' => [],
                'applicationFunnel' => [],
                'interviewFunnel' => [],
                'hireCount' => 0,
            ];
        }

        $companyId = (int) $company['id'];
        $applications = JobApplication::forCompany($companyId);
        $applicationFunnel = $this->countByStatus($applications);

        return [
            'jobFunnel' => $this->countByStatus(JobPosting::forCompany($companyId)),
            'applicationFunnel' => $applicationFunnel,
            'interviewFunnel' => $this->countByStatus(InterviewSession::forCompany($companyId)),
            'hireCount' => $applicationFunnel['selected'] ?? 0,
        ];
    }

    protected function instituteData(int $userId): array
    {
        $institute = Institute::findByUserId($userId);

        if ($institute === null) {
            return ['enrollmentFunnel' => [], 'courseFunnel' => []];
        }

        $instituteId = (int) $institute['id'];

        return [
            'enrollmentFunnel' => $this->countByStatus(InstituteEnrollmentRequest::forInstitute($instituteId)),
            'courseFunnel' => $this->countByStatus(InstituteCourse::forInstitute($instituteId)),
        ];
    }

    protected function collegeData(int $userId): array
    {
        $college = College::findByUserId($userId);

        if ($college === null) {
            return ['registrationFunnel' => [], 'driveFunnel' => [], 'hireCount' => 0];
        }

        $collegeId = (int) $college['id'];
        $registrationFunnel = $this->countByStatus(CollegeDriveRegistration::forCollege($collegeId));

        return [
            'registrationFunnel' => $registrationFunnel,
            'driveFunnel' => $this->countByStatus(CollegeCampusDrive::forCollege($collegeId)),
            'hireCount' => $registrationFunnel['selected'] ?? 0,
        ];
    }

    protected function adminData(): array
    {
        return [
            'usersByRole' => User::countByRole(),
            'jobFunnel' => JobPosting::countByStatus(),
            'applicationFunnel' => JobApplication::countByStatus(),
            'driveRegistrationFunnel' => CollegeDriveRegistration::countByStatus(),
            'instituteCount' => Institute::count(),
            'collegeCount' => College::count(),
            'companyCount' => Company::count(),
        ];
    }

    protected function countByStatus(array $rows): array
    {
        return array_count_values(array_column($rows, 'status'));
    }
}
