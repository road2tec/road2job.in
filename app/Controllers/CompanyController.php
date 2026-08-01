<?php

namespace App\Controllers;

use App\Models\Company;
use App\Services\FileUploadService;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class CompanyController extends Controller
{
    public function show(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $this->view('dashboard/employer/company', [
            'user' => $sessionUser,
            'company' => Company::findByUserId($userId),
        ], 'employer');
    }

    public function update(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $data = $request->only([
            'name', 'industry', 'company_size', 'website', 'description', 'founded_year', 'headquarters_location',
        ]);

        $validator = Validator::make($data);
        $validator->validate([
            'name' => 'required|min:2|max:150',
            'website' => 'url',
            'founded_year' => 'numeric',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/company');
            return;
        }

        if (($data['founded_year'] ?? '') === '') {
            $data['founded_year'] = null;
        }

        $logoFile = $request->file('logo');
        if ($logoFile !== null && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $data['logo_path'] = (new FileUploadService())->upload($logoFile, 'logo');
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('/dashboard/company');
                return;
            }
        }

        Company::saveForUser($userId, $data);

        Session::flash('success', 'Company profile updated.');
        $this->redirect('/dashboard/company');
    }

    public function submitVerification(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $documentPath = null;
        $documentFile = $request->file('verification_document');

        if ($documentFile !== null && ($documentFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $documentPath = (new FileUploadService())->upload($documentFile, 'document');
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('/dashboard/company');
                return;
            }
        }

        Company::markVerificationPending($userId, $documentPath);

        Session::flash('success', 'Verification request submitted. We\'ll review it soon.');
        $this->redirect('/dashboard/company');
    }
}
