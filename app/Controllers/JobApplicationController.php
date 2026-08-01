<?php

namespace App\Controllers;

use App\Models\Company;
use App\Models\InterviewSession;
use App\Models\JobApplication;
use App\Models\JobApplicationEvent;
use App\Models\JobPosting;
use App\Models\StudentSkill;
use App\Services\JobMatchScorer;
use App\Services\ResumeCompiler;
use Core\Controller;
use Core\Request;
use Core\Session;

class JobApplicationController extends Controller
{
    /**
     * Student-side action reached from the public job page. Inline
     * auth/role check rather than route-group middleware, since the GET
     * pages around it are public.
     */
    public function apply(Request $request, string $jobPostingId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in as a student to apply.');
            $this->redirect('/login');
            return;
        }

        if ($sessionUser['role_slug'] !== 'student') {
            Session::flash('error', 'Only student accounts can apply to jobs.');
            $this->redirect('/jobs');
            return;
        }

        $job = JobPosting::findPublished((int) $jobPostingId);

        if ($job === null) {
            Session::flash('error', 'That job is not available.');
            $this->redirect('/jobs');
            return;
        }

        $studentId = (int) $sessionUser['id'];

        if (JobApplication::hasApplied((int) $jobPostingId, $studentId)) {
            Session::flash('error', 'You have already applied to this job.');
            $this->redirect('/jobs/' . $jobPostingId);
            return;
        }

        $coverNote = trim((string) $request->input('cover_note', ''));
        $snapshot = json_encode(ResumeCompiler::compile($studentId));

        $applicationId = (int) JobApplication::create(
            (int) $jobPostingId,
            $studentId,
            (int) $job['company_id'],
            $snapshot,
            $coverNote !== '' ? $coverNote : null
        );

        JobApplicationEvent::record($applicationId, 'applied', null, 'applied');

        Session::flash('success', 'Application submitted! You can track its status below.');
        $this->redirect('/dashboard/applications');
    }

    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $studentId = (int) $sessionUser['id'];

        $applications = JobApplication::forStudent($studentId);
        $studentSkillNames = array_map(fn ($row) => $row['skill_name'], StudentSkill::forUser($studentId));

        foreach ($applications as &$application) {
            $job = [
                'title' => $application['job_title'],
                'description' => $application['job_description'],
                'requirements' => $application['job_requirements'],
            ];
            $application['matchScore'] = JobMatchScorer::scoreForStudent($job, $studentSkillNames);
            $application['timeline'] = $this->buildTimeline((int) $application['id']);
        }
        unset($application);

        $this->view('dashboard/student/applications', [
            'user' => $sessionUser,
            'applications' => $applications,
        ], 'student');
    }

    /**
     * Merges the real event log with interview_sessions' own
     * requested_at/completed_at (not duplicated into the event log, joined
     * in here) into one chronological timeline - every point is a genuine
     * recorded fact, nothing fabricated or inferred.
     */
    protected function buildTimeline(int $applicationId): array
    {
        $labels = [
            'applied' => ['label' => 'Application submitted', 'icon' => 'bi-send-check'],
            'viewed' => ['label' => 'Viewed by recruiter', 'icon' => 'bi-eye'],
            'under_review' => ['label' => 'Application under review', 'icon' => 'bi-hourglass-split'],
            'shortlisted' => ['label' => 'Shortlisted', 'icon' => 'bi-star-fill'],
            'rejected' => ['label' => 'Not selected this time', 'icon' => 'bi-x-circle'],
            'selected' => ['label' => 'Selected!', 'icon' => 'bi-trophy-fill'],
        ];

        $timeline = [];

        foreach (JobApplicationEvent::forApplication($applicationId) as $event) {
            $key = $event['event_type'] === 'status_changed' ? $event['to_status'] : $event['event_type'];
            $meta = $labels[$key] ?? ['label' => ucfirst(str_replace('_', ' ', (string) $key)), 'icon' => 'bi-dot'];
            $timeline[] = ['label' => $meta['label'], 'icon' => $meta['icon'], 'at' => $event['event_at']];
        }

        $interview = InterviewSession::findByApplication($applicationId);
        if ($interview !== null) {
            $timeline[] = ['label' => 'Interview requested', 'icon' => 'bi-camera-video', 'at' => $interview['requested_at']];
            if ($interview['completed_at'] !== null) {
                $timeline[] = ['label' => 'Interview completed', 'icon' => 'bi-check-circle', 'at' => $interview['completed_at']];
            }
        }

        usort($timeline, fn ($a, $b) => strtotime($a['at']) <=> strtotime($b['at']));

        return $timeline;
    }

    public function manageIndex(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);

        $this->view('dashboard/employer/applicants', [
            'user' => $sessionUser,
            'applications' => $company !== null ? JobApplication::forCompany((int) $company['id']) : [],
        ], 'employer');
    }

    public function updateStatus(Request $request, string $id): void
    {
        $record = $this->ownedApplication($id);

        if ($record === null) {
            return;
        }

        $status = $request->input('status');

        if (!in_array($status, ['applied', 'under_review', 'shortlisted', 'rejected', 'selected'], true)) {
            Session::flash('error', 'Invalid status.');
            $this->redirect('/dashboard/applicants');
            return;
        }

        $previousStatus = $record['status'];

        JobApplication::update((int) $id, ['status' => $status]);

        if ($status !== $previousStatus) {
            JobApplicationEvent::record((int) $id, 'status_changed', $previousStatus, $status);
        }

        Session::flash('success', 'Status updated.');
        $this->redirect('/dashboard/applicants');
    }

    public function showApplicant(Request $request, string $id): void
    {
        $record = $this->ownedApplication($id);

        if ($record === null) {
            return;
        }

        if (!JobApplicationEvent::hasEventType((int) $id, 'viewed')) {
            JobApplicationEvent::record((int) $id, 'viewed');
        }

        $snapshot = json_decode((string) $record['resume_snapshot'], true) ?? [];

        $this->view('resume/templates/professional', array_merge($snapshot, [
            'isOwnerView' => false,
        ]), 'print');
    }

    protected function ownedApplication(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);
        $record = JobApplication::find((int) $id);

        if ($record === null || $company === null || (int) $record['company_id'] !== (int) $company['id']) {
            Session::flash('error', 'That application could not be found.');
            $this->redirect('/dashboard/applicants');
            return null;
        }

        return $record;
    }
}
