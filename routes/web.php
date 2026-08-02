<?php

use App\Controllers\AccountController;
use App\Controllers\AchievementController;
use App\Controllers\AdminAuditLogController;
use App\Controllers\AdminChatController;
use App\Controllers\AdminCompanyController;
use App\Controllers\AdminDirectoryController;
use App\Controllers\AdminEventController;
use App\Controllers\AdminJobController;
use App\Controllers\AdminLogController;
use App\Controllers\AdminNotificationController;
use App\Controllers\AdminReportController;
use App\Controllers\AdminSecurityController;
use App\Controllers\AdminSettingController;
use App\Controllers\AdminSystemController;
use App\Controllers\AdminUserController;
use App\Controllers\AnalyticsController;
use App\Controllers\AssessmentController;
use App\Controllers\AssignmentController;
use App\Controllers\AuthController;
use App\Controllers\BlogController;
use App\Controllers\CareerServicesController;
use App\Controllers\CertificateController;
use App\Controllers\ChatController;
use App\Controllers\CollegeAlumniController;
use App\Controllers\CollegeCampusDriveController;
use App\Controllers\CollegeController;
use App\Controllers\CollegeDepartmentStatController;
use App\Controllers\CollegeDriveRegistrationController;
use App\Controllers\CompanyController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Controllers\EducationController;
use App\Controllers\EventController;
use App\Controllers\ExperienceController;
use App\Controllers\InstituteCertificateController;
use App\Controllers\InstituteController;
use App\Controllers\InstituteCourseController;
use App\Controllers\InstituteDiscoveryController;
use App\Controllers\InstituteEnrollmentController;
use App\Controllers\InstituteFacultyController;
use App\Controllers\InstituteGalleryController;
use App\Controllers\InstitutePlacementController;
use App\Controllers\InstituteReviewController;
use App\Controllers\InstituteUpdateController;
use App\Controllers\CommunityController;
use App\Controllers\InterviewController;
use App\Controllers\JobAlertController;
use App\Controllers\JobApplicationController;
use App\Controllers\JobPostingController;
use App\Controllers\LanguageController;
use App\Controllers\MentorController;
use App\Controllers\MockInterviewController;
use App\Controllers\PageController;
use App\Controllers\PortfolioController;
use App\Controllers\ProfileController;
use App\Controllers\ProjectController;
use App\Controllers\ResearchController;
use App\Controllers\ResumeController;
use App\Controllers\RoadmapController;
use App\Controllers\SavedJobController;
use App\Controllers\SettingsController;
use App\Controllers\SkillController;

/** @var Core\Router $router */

$router->get('/', [PageController::class, 'home']);
$router->get('/robots.txt', [PageController::class, 'robots']);
$router->get('/sitemap.xml', [PageController::class, 'sitemap']);
$router->get('/about', [PageController::class, 'about']);
$router->get('/faq', [PageController::class, 'faq']);
$router->get('/privacy-policy', [PageController::class, 'privacy']);
$router->get('/terms-of-service', [PageController::class, 'terms']);
$router->get('/careers', [PageController::class, 'careers']);
$router->get('/u/{username}', [PortfolioController::class, 'show']);
$router->get('/u/{username}/resume', [PortfolioController::class, 'resume']);
$router->get('/jobs', [PageController::class, 'jobs']);
$router->get('/jobs/{id}', [PageController::class, 'jobShow']);
$router->get('/internships', [PageController::class, 'internships']);
$router->get('/companies', [PageController::class, 'companies']);
$router->get('/companies/{id}', [PageController::class, 'companyShow']);
$router->get('/institutes', [PageController::class, 'institutes']);
$router->get('/institutes/compare', [InstituteDiscoveryController::class, 'compare']);
$router->get('/institutes/{id}', [PageController::class, 'instituteShow']);
$router->get('/colleges', [PageController::class, 'colleges']);
$router->get('/colleges/{id}', [PageController::class, 'collegeShow']);
$router->get('/community', [CommunityController::class, 'index']);
$router->get('/community/new', [CommunityController::class, 'create']);
$router->get('/community/{id}', [CommunityController::class, 'show']);
$router->get('/mentors', [MentorController::class, 'directory']);
$router->get('/mentors/{id}', [MentorController::class, 'show']);
$router->get('/research-hub', [PageController::class, 'researchHub']);
$router->get('/research-hub/{id}', [PageController::class, 'researchItemShow']);
$router->get('/roadmaps', [RoadmapController::class, 'index']);
$router->get('/roadmaps/{id}', [RoadmapController::class, 'show']);
$router->get('/events', [EventController::class, 'index']);
$router->get('/events/{id}', [EventController::class, 'show']);
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{id}', [BlogController::class, 'show']);

$router->group(['middleware' => ['csrf']], function (Core\Router $router) {
    $router->get('/contact', [ContactController::class, 'show']);
    $router->post('/contact', [ContactController::class, 'submit']);

    $router->post('/institutes/{id}/reviews', [InstituteReviewController::class, 'store']);
    $router->post('/institutes/courses/{courseId}/enroll', [InstituteEnrollmentController::class, 'request']);
    $router->post('/colleges/drives/{driveId}/register', [CollegeDriveRegistrationController::class, 'register']);
    $router->post('/jobs/{id}/apply', [JobApplicationController::class, 'apply']);
    $router->post('/jobs/{id}/save', [SavedJobController::class, 'toggle']);

    $router->post('/community', [CommunityController::class, 'store']);
    $router->post('/community/{postId}/replies', [CommunityController::class, 'reply']);
    $router->post('/community/{postId}/replies/{replyId}/accept', [CommunityController::class, 'acceptReply']);
    $router->post('/mentors/{id}/request', [MentorController::class, 'request']);
    $router->post('/events/{id}/register', [EventController::class, 'register']);
});

$router->group(['middleware' => ['guest', 'csrf']], function (Core\Router $router) {
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);

    $router->get('/verify-otp', [AuthController::class, 'showVerifyOtp']);
    $router->post('/verify-otp', [AuthController::class, 'verifyOtp']);
    $router->post('/resend-otp', [AuthController::class, 'resendOtp']);

    $router->get('/verify-email', [AuthController::class, 'verifyEmail']);
    $router->post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);

    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'sendResetLink']);

    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword']);
});

// /login is deliberately NOT behind 'guest' middleware - an already-authenticated
// session must still be able to reach the login form and submit different
// credentials to switch accounts. attemptLogin() -> establishSession() already
// correctly regenerates the session ID and overwrites '_user', so this is safe;
// previously 'guest' bounced any authenticated request (GET or POST) straight to
// /dashboard, meaning a logged-in user could never switch to a different account
// without first explicitly logging out.
$router->group(['middleware' => ['csrf']], function (Core\Router $router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
});

$router->group(['middleware' => ['auth', 'csrf']], function (Core\Router $router) {
    $router->post('/logout', [AuthController::class, 'logout']);
    $router->get('/dashboard', [DashboardController::class, 'index']);

    $router->get('/dashboard/events', [EventController::class, 'hub']);
    $router->get('/dashboard/events/{eventId}/certificate', [EventController::class, 'certificate']);

    $router->get('/dashboard/analytics', [AnalyticsController::class, 'index']);
    $router->get('/dashboard/revenue-analytics', function (Core\Request $request) {
        (new DashboardController())->comingSoon($request, 'revenue-analytics');
    });

    $router->get('/account/security', [AccountController::class, 'security']);
    $router->post('/account/security/revoke/{id}', [AccountController::class, 'revokeSession']);
    $router->post('/account/security/revoke-all', [AccountController::class, 'revokeAllOtherSessions']);
});

$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'csrf', 'role:admin,super_admin']], function (Core\Router $router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);

    $router->get('/users', [AdminUserController::class, 'index']);
    $router->post('/users/{id}', [AdminUserController::class, 'updateStatus']);

    $router->get('/companies', [AdminCompanyController::class, 'index']);
    $router->post('/companies/{id}', [AdminCompanyController::class, 'updateVerification']);

    $router->get('/institutes', [AdminDirectoryController::class, 'institutes']);
    $router->post('/institutes/{id}/status', [AdminDirectoryController::class, 'updateInstituteStatus']);
    $router->post('/institutes/{id}/verification', [AdminDirectoryController::class, 'updateInstituteVerification']);
    $router->post('/institute-placements/{id}/status', [AdminDirectoryController::class, 'updatePlacementStatus']);
    $router->post('/institute-updates/{id}/status', [AdminDirectoryController::class, 'updateUpdateStatus']);
    $router->get('/colleges', [AdminDirectoryController::class, 'colleges']);
    $router->get('/applications', [AdminDirectoryController::class, 'applications']);

    $router->get('/chats', [AdminChatController::class, 'index']);
    $router->get('/chats/{id}', [AdminChatController::class, 'show']);
    $router->post('/chats/{id}/status', [AdminChatController::class, 'updateStatus']);

    $router->get('/jobs', [AdminJobController::class, 'index']);
    $router->post('/jobs/{id}', [AdminJobController::class, 'updateStatus']);
    $router->post('/jobs/{id}/delete', [AdminJobController::class, 'destroy']);

    $router->get('/events', [AdminEventController::class, 'index']);
    $router->post('/events/{id}/delete', [AdminEventController::class, 'destroy']);

    $router->get('/blog', [BlogController::class, 'adminIndex']);
    $router->post('/blog', [BlogController::class, 'store']);
    $router->post('/blog/{id}', [BlogController::class, 'update']);
    $router->post('/blog/{id}/delete', [BlogController::class, 'destroy']);

    $router->get('/notifications', [AdminNotificationController::class, 'index']);
    $router->post('/notifications', [AdminNotificationController::class, 'store']);

    $router->get('/reports', [AdminReportController::class, 'index']);
    $router->get('/reports/export/{type}', [AdminReportController::class, 'export']);

    $router->get('/security', [AdminSecurityController::class, 'index']);
    $router->post('/security/{id}/revoke', [AdminSecurityController::class, 'revokeSessions']);

    $router->get('/audit-logs', [AdminAuditLogController::class, 'index']);

    $router->get('/settings', [AdminSettingController::class, 'index']);
    $router->post('/settings', [AdminSettingController::class, 'update']);

    foreach (['payments', 'advertisements'] as $adminComingSoonKey) {
        $router->get('/' . $adminComingSoonKey, function (Core\Request $request) use ($adminComingSoonKey) {
            (new DashboardController())->comingSoon($request, $adminComingSoonKey);
        });
    }
});

$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'csrf', 'role:super_admin']], function (Core\Router $router) {
    $router->get('/roles', [AdminSystemController::class, 'roles']);
    $router->get('/system', [AdminSystemController::class, 'index']);
    $router->post('/system/maintenance-mode', [AdminSystemController::class, 'updateMaintenanceMode']);
    $router->get('/health', [AdminSystemController::class, 'health']);
    $router->get('/logs', [AdminLogController::class, 'index']);
    $router->post('/logs/cleanup', [AdminLogController::class, 'cleanup']);

    foreach (['api-configuration', 'cron-jobs', 'database-backup', 'fraud-detection'] as $superAdminComingSoonKey) {
        $router->get('/' . $superAdminComingSoonKey, function (Core\Request $request) use ($superAdminComingSoonKey) {
            (new DashboardController())->comingSoon($request, $superAdminComingSoonKey);
        });
    }
});

$router->group(['middleware' => ['auth', 'csrf', 'role:student']], function (Core\Router $router) {
    $router->get('/dashboard/profile', [ProfileController::class, 'show']);
    $router->post('/dashboard/profile', [ProfileController::class, 'update']);

    $router->get('/dashboard/resume', [ResumeController::class, 'preview']);
    $router->post('/dashboard/resume/template', [ResumeController::class, 'updateTemplate']);

    $router->get('/dashboard/portfolio', [PortfolioController::class, 'manage']);
    $router->post('/dashboard/portfolio/manage', [PortfolioController::class, 'updateManager']);

    $router->get('/dashboard/applications', [JobApplicationController::class, 'index']);
    $router->get('/dashboard/saved-jobs', [SavedJobController::class, 'index']);

    $router->get('/dashboard/my-interviews', [InterviewController::class, 'studentIndex']);
    $router->get('/dashboard/my-interviews/{id}', [InterviewController::class, 'studentShow']);
    $router->post('/dashboard/my-interviews/{id}/finish', [InterviewController::class, 'finishSession']);

    $router->get('/dashboard/career-services', [CareerServicesController::class, 'hub']);

    $router->get('/dashboard/mock-interviews', [MockInterviewController::class, 'index']);
    $router->post('/dashboard/mock-interviews/start', [MockInterviewController::class, 'start']);
    $router->get('/dashboard/mock-interviews/{id}', [MockInterviewController::class, 'show']);
    $router->post('/dashboard/mock-interviews/{id}/finish', [MockInterviewController::class, 'finishSession']);

    $router->get('/dashboard/job-alerts', [JobAlertController::class, 'index']);
    $router->post('/dashboard/job-alerts', [JobAlertController::class, 'store']);
    $router->post('/dashboard/job-alerts/{id}/delete', [JobAlertController::class, 'destroy']);

    $router->get('/dashboard/assessments', [AssessmentController::class, 'index']);
    $router->post('/dashboard/assessments/{category}/start', [AssessmentController::class, 'start']);
    $router->get('/dashboard/assessments/attempts/{id}', [AssessmentController::class, 'show']);
    $router->post('/dashboard/assessments/attempts/{id}/submit', [AssessmentController::class, 'submit']);
    $router->get('/dashboard/assessments/attempts/{id}/result', [AssessmentController::class, 'result']);
    $router->get('/dashboard/assessments/attempts/{id}/certificate', [AssessmentController::class, 'certificate']);

    $router->get('/dashboard/research', [ResearchController::class, 'index']);
    $router->post('/dashboard/research', [ResearchController::class, 'store']);
    $router->post('/dashboard/research/{id}', [ResearchController::class, 'update']);
    $router->post('/dashboard/research/{id}/delete', [ResearchController::class, 'destroy']);

    $router->get('/dashboard/my-learning', [AssignmentController::class, 'myLearning']);
    $router->get('/dashboard/my-learning/{courseId}', [AssignmentController::class, 'courseShow']);
    $router->post('/dashboard/my-learning/{courseId}/assignments/{assignmentId}/submit', [AssignmentController::class, 'submit']);
    $router->get('/dashboard/my-learning/{courseId}/certificate', [InstituteEnrollmentController::class, 'certificate']);

    $router->get('/dashboard/settings', [SettingsController::class, 'show']);
    $router->post('/dashboard/settings/password', [SettingsController::class, 'updatePassword']);
    $router->post('/dashboard/settings/preferences', [SettingsController::class, 'updatePreferences']);

    // Registered before the generic {slug}/{id} loop below so it isn't
    // shadowed by "skills/{id}" matching "bulk" as an id.
    $router->post('/dashboard/profile/skills/bulk', [SkillController::class, 'bulkStore']);

    $studentResources = [
        'education' => EducationController::class,
        'skills' => SkillController::class,
        'projects' => ProjectController::class,
        'experience' => ExperienceController::class,
        'certificates' => CertificateController::class,
        'achievements' => AchievementController::class,
        'languages' => LanguageController::class,
    ];

    foreach ($studentResources as $slug => $controllerClass) {
        $router->post("/dashboard/profile/{$slug}", [$controllerClass, 'store']);
        $router->post("/dashboard/profile/{$slug}/{id}", [$controllerClass, 'update']);
        $router->post("/dashboard/profile/{$slug}/{id}/delete", [$controllerClass, 'destroy']);
    }

    foreach (['career-passport'] as $studentComingSoonKey) {
        $router->get('/dashboard/' . $studentComingSoonKey, function (Core\Request $request) use ($studentComingSoonKey) {
            (new DashboardController())->comingSoon($request, $studentComingSoonKey);
        });
    }
});

$router->group(['middleware' => ['auth', 'csrf', 'role:employer']], function (Core\Router $router) {
    $router->get('/dashboard/company', [CompanyController::class, 'show']);
    $router->post('/dashboard/company', [CompanyController::class, 'update']);
    $router->post('/dashboard/company/verification', [CompanyController::class, 'submitVerification']);

    $router->get('/dashboard/jobs', [JobPostingController::class, 'index']);
    $router->post('/dashboard/jobs', [JobPostingController::class, 'store']);
    $router->post('/dashboard/jobs/{id}', [JobPostingController::class, 'update']);
    $router->post('/dashboard/jobs/{id}/delete', [JobPostingController::class, 'destroy']);

    $router->get('/dashboard/applicants', [JobApplicationController::class, 'manageIndex']);
    $router->post('/dashboard/applicants/{id}', [JobApplicationController::class, 'updateStatus']);
    $router->get('/dashboard/applicants/{id}', [JobApplicationController::class, 'showApplicant']);
    $router->post('/dashboard/applicants/{applicationId}/request-interview', [InterviewController::class, 'request']);
    $router->post('/dashboard/applicants/{applicationId}/chat-request', [ChatController::class, 'sendRequest']);

    $router->get('/dashboard/interviews', [InterviewController::class, 'manageIndex']);
    $router->get('/dashboard/interviews/{id}', [InterviewController::class, 'showForEmployer']);
    $router->post('/dashboard/interviews/{id}/score', [InterviewController::class, 'submitScore']);
});

// Chat inbox/thread/request-response actions are identical regardless of
// which side of the conversation you're on - only the one-way "send a
// request" trigger (role:employer group above) is asymmetric.
$router->group(['middleware' => ['auth', 'csrf', 'role:student,employer']], function (Core\Router $router) {
    $router->get('/dashboard/chat', [ChatController::class, 'inbox']);
    $router->get('/dashboard/chat/requests', [ChatController::class, 'requestsIndex']);
    $router->post('/dashboard/chat/requests/{id}', [ChatController::class, 'respondToRequest']);
    $router->get('/dashboard/chat/{threadId}', [ChatController::class, 'show']);
    $router->post('/dashboard/chat/{threadId}/messages', [ChatController::class, 'sendMessage']);
    $router->get('/dashboard/chat/{threadId}/poll', [ChatController::class, 'poll']);
    $router->post('/dashboard/chat/{threadId}/read', [ChatController::class, 'markRead']);
});

$router->group(['middleware' => ['auth', 'csrf', 'role:institute']], function (Core\Router $router) {
    $router->get('/dashboard/institute', [InstituteController::class, 'show']);
    $router->post('/dashboard/institute', [InstituteController::class, 'update']);

    $router->get('/dashboard/institute/courses', [InstituteCourseController::class, 'index']);

    $router->get('/dashboard/institute/updates', [InstituteUpdateController::class, 'index']);

    $router->get('/dashboard/institute/enrollments', [InstituteEnrollmentController::class, 'index']);
    $router->post('/dashboard/institute/enrollments/{id}', [InstituteEnrollmentController::class, 'updateStatus']);

    $router->get('/dashboard/institute/courses/{courseId}/assignments', [AssignmentController::class, 'index']);
    $router->post('/dashboard/institute/courses/{courseId}/assignments', [AssignmentController::class, 'store']);
    $router->post('/dashboard/institute/assignments/{id}', [AssignmentController::class, 'update']);
    $router->post('/dashboard/institute/assignments/{id}/delete', [AssignmentController::class, 'destroy']);
    $router->get('/dashboard/institute/assignments/{assignmentId}/submissions', [AssignmentController::class, 'submissions']);
    $router->post('/dashboard/institute/submissions/{submissionId}/review', [AssignmentController::class, 'review']);

    $router->get('/dashboard/institute/roadmaps', [RoadmapController::class, 'manage']);
    $router->post('/dashboard/institute/roadmaps', [RoadmapController::class, 'store']);
    $router->post('/dashboard/institute/roadmaps/{id}/delete', [RoadmapController::class, 'destroy']);
    $router->post('/dashboard/institute/roadmaps/{roadmapId}/milestones', [RoadmapController::class, 'addMilestone']);
    $router->post('/dashboard/institute/roadmaps/milestones/{id}/delete', [RoadmapController::class, 'deleteMilestone']);

    $instituteResources = [
        'courses' => InstituteCourseController::class,
        'faculty' => InstituteFacultyController::class,
        'gallery' => InstituteGalleryController::class,
        'certificates' => InstituteCertificateController::class,
        'placements' => InstitutePlacementController::class,
        'updates' => InstituteUpdateController::class,
    ];

    foreach ($instituteResources as $slug => $controllerClass) {
        $router->post("/dashboard/institute/{$slug}", [$controllerClass, 'store']);
        $router->post("/dashboard/institute/{$slug}/{id}", [$controllerClass, 'update']);
        $router->post("/dashboard/institute/{$slug}/{id}/delete", [$controllerClass, 'destroy']);
    }
});

$router->group(['middleware' => ['auth', 'csrf', 'role:college']], function (Core\Router $router) {
    $router->get('/dashboard/college', [CollegeController::class, 'show']);
    $router->post('/dashboard/college', [CollegeController::class, 'update']);

    $router->get('/dashboard/college/drives', [CollegeCampusDriveController::class, 'index']);

    $router->get('/dashboard/college/registrations', [CollegeDriveRegistrationController::class, 'index']);
    $router->post('/dashboard/college/registrations/{id}', [CollegeDriveRegistrationController::class, 'updateStatus']);

    $collegeResources = [
        'drives' => CollegeCampusDriveController::class,
        'department-stats' => CollegeDepartmentStatController::class,
        'alumni' => CollegeAlumniController::class,
    ];

    foreach ($collegeResources as $slug => $controllerClass) {
        $router->post("/dashboard/college/{$slug}", [$controllerClass, 'store']);
        $router->post("/dashboard/college/{$slug}/{id}", [$controllerClass, 'update']);
        $router->post("/dashboard/college/{$slug}/{id}/delete", [$controllerClass, 'destroy']);
    }
});

$router->group(['middleware' => ['auth', 'csrf', 'role:mentor']], function (Core\Router $router) {
    $router->get('/dashboard/mentor', [MentorController::class, 'profile']);
    $router->post('/dashboard/mentor', [MentorController::class, 'updateProfile']);

    $router->get('/dashboard/mentor/requests', [MentorController::class, 'requests']);
    $router->post('/dashboard/mentor/requests/{id}', [MentorController::class, 'updateRequestStatus']);
    $router->get('/dashboard/mentor/requests/{id}/resume', [MentorController::class, 'viewSnapshot']);
});

$router->group(['middleware' => ['auth', 'csrf', 'role:employer,institute,college,mentor']], function (Core\Router $router) {
    $router->post('/dashboard/events', [EventController::class, 'store']);
    $router->post('/dashboard/events/{id}', [EventController::class, 'update']);
    $router->post('/dashboard/events/{id}/delete', [EventController::class, 'destroy']);
    $router->get('/dashboard/events/{eventId}/registrations', [EventController::class, 'registrations']);
    $router->post('/dashboard/events/registrations/{registrationId}/attend', [EventController::class, 'markAttended']);
});
