<?php

namespace App\Controllers;

use App\Models\CollegeDriveRegistration;
use App\Models\Institute;
use App\Models\College;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminReportController extends Controller
{
    protected const TYPES = ['users', 'companies', 'jobs', 'applications'];

    public function index(Request $request): void
    {
        $this->view('dashboard/admin/reports', [
            'user' => Session::get('_user'),
            'usersByRole' => User::countByRole(),
            'jobFunnel' => JobPosting::countByStatus(),
            'applicationFunnel' => JobApplication::countByStatus(),
            'driveRegistrationFunnel' => CollegeDriveRegistration::countByStatus(),
            'instituteCount' => Institute::count(),
            'collegeCount' => College::count(),
            'companyCount' => Company::count(),
            'types' => self::TYPES,
        ], 'admin');
    }

    public function export(Request $request, string $type): void
    {
        if (!in_array($type, self::TYPES, true)) {
            Session::flash('error', 'Unknown report type.');
            $this->redirect('/admin/reports');
            return;
        }

        [$filename, $headers, $rows] = match ($type) {
            'users' => ['users.csv', ['ID', 'Name', 'Email', 'Role', 'Status', 'Joined'], array_map(
                fn ($row) => [$row['id'], $row['full_name'], $row['email'], $row['role_label'], $row['status'], $row['created_at']],
                User::adminListing()
            )],
            'companies' => ['companies.csv', ['ID', 'Name', 'Owner', 'Owner Email', 'Verification', 'Created'], array_map(
                fn ($row) => [$row['id'], $row['name'], $row['owner_name'], $row['owner_email'], $row['verification_status'], $row['created_at']],
                Company::adminListing()
            )],
            'jobs' => ['jobs.csv', ['ID', 'Title', 'Company', 'Status', 'Created'], array_map(
                fn ($row) => [$row['id'], $row['title'], $row['company_name'], $row['status'], $row['created_at']],
                JobPosting::adminListing()
            )],
            'applications' => ['applications.csv', ['ID', 'Job', 'Company', 'Student', 'Status', 'Applied'], array_map(
                fn ($row) => [$row['id'], $row['job_title'], $row['company_name'], $row['student_name'], $row['status'], $row['created_at']],
                JobApplication::adminListing()
            )],
        };

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }
}
