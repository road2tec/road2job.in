<?php

namespace App\Controllers;

use App\Models\Company;
use App\Models\JobPosting;
use App\Services\JobAlertMatcher;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class JobPostingController extends Controller
{
    protected function fields(): array
    {
        return [
            'title', 'type', 'location', 'is_remote', 'description', 'requirements',
            'min_salary', 'max_salary', 'experience_level', 'application_deadline', 'status',
        ];
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|min:2|max:150',
            'type' => 'required|in:full_time,part_time,internship,contract,remote',
            'experience_level' => 'required|in:fresher,junior,mid,senior',
            'status' => 'required|in:draft,published,closed',
            'application_deadline' => 'date',
            'min_salary' => 'numeric',
            'max_salary' => 'numeric',
        ];
    }

    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];
        $company = Company::findByUserId($userId);

        $this->view('dashboard/employer/jobs', [
            'user' => $sessionUser,
            'company' => $company,
            'jobs' => $company !== null ? JobPosting::forCompany((int) $company['id']) : [],
        ], 'employer');
    }

    public function store(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);

        if ($company === null) {
            Session::flash('error', 'Please set up your company profile before posting a job.');
            $this->redirect('/dashboard/company');
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data['company_id'] = (int) $company['id'];
        $data['is_remote'] = $request->input('is_remote') ? 1 : 0;

        if ($data['status'] === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        JobPosting::insert($data);

        if ($data['status'] === 'published') {
            JobAlertMatcher::notifyForNewPosting($data);
        }

        Session::flash('success', 'Job posting created.');
        $this->redirect('/dashboard/jobs');
    }

    public function update(Request $request, string $id): void
    {
        $record = $this->ownedRecord($request, $id);

        if ($record === null) {
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data['is_remote'] = $request->input('is_remote') ? 1 : 0;

        $isNewlyPublished = $data['status'] === 'published' && $record['published_at'] === null;

        if ($isNewlyPublished) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        JobPosting::update((int) $id, $data);

        if ($isNewlyPublished) {
            JobAlertMatcher::notifyForNewPosting($data);
        }

        Session::flash('success', 'Job posting updated.');
        $this->redirect('/dashboard/jobs');
    }

    public function destroy(Request $request, string $id): void
    {
        $record = $this->ownedRecord($request, $id);

        if ($record !== null) {
            JobPosting::delete((int) $id);
            Session::flash('success', 'Job posting deleted.');
        }

        $this->redirect('/dashboard/jobs');
    }

    protected function validated(Request $request): ?array
    {
        $data = $request->only($this->fields());
        unset($data['is_remote']);

        $validator = Validator::make($data);
        $validator->validate($this->rules());

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/jobs');
            return null;
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    protected function ownedRecord(Request $request, string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);
        $record = JobPosting::find((int) $id);

        if ($record === null || $company === null || (int) $record['company_id'] !== (int) $company['id']) {
            Session::flash('error', 'That job posting could not be found.');
            $this->redirect('/dashboard/jobs');
            return null;
        }

        return $record;
    }
}
