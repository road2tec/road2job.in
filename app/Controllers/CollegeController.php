<?php

namespace App\Controllers;

use App\Models\College;
use App\Models\CollegeAlumnus;
use App\Models\CollegeDepartmentStat;
use App\Services\FileUploadService;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class CollegeController extends Controller
{
    public function show(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];
        $college = College::findByUserId($userId);
        $collegeId = $college !== null ? (int) $college['id'] : null;

        $this->view('dashboard/college/profile', [
            'user' => $sessionUser,
            'college' => $college,
            'departmentStats' => $collegeId !== null ? CollegeDepartmentStat::forCollege($collegeId) : [],
            'alumni' => $collegeId !== null ? CollegeAlumnus::forCollege($collegeId) : [],
        ], 'employer');
    }

    public function update(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $data = $request->only([
            'name', 'description', 'established_year', 'website', 'location',
        ]);

        $validator = Validator::make($data);
        $validator->validate([
            'name' => 'required|min:2|max:150',
            'website' => 'url',
            'established_year' => 'numeric',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/college');
            return;
        }

        if (($data['established_year'] ?? '') === '') {
            $data['established_year'] = null;
        }

        $logoFile = $request->file('logo');
        if ($logoFile !== null && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $data['logo_path'] = (new FileUploadService())->upload($logoFile, 'logo');
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('/dashboard/college');
                return;
            }
        }

        College::saveForUser($userId, $data);

        Session::flash('success', 'College profile updated.');
        $this->redirect('/dashboard/college');
    }
}
