<?php

namespace App\Controllers;

use App\Models\Institute;
use App\Models\InstituteCertificate;
use App\Models\InstituteFaculty;
use App\Models\InstituteGallery;
use App\Models\InstitutePlacement;
use App\Models\InstituteUpdate;
use App\Services\FileUploadService;
use App\Services\InstituteProfileScorer;
use App\Services\InstituteRankingScorer;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class InstituteController extends Controller
{
    public function show(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];
        $institute = Institute::findByUserId($userId);
        $instituteId = $institute !== null ? (int) $institute['id'] : null;

        $placements = $instituteId !== null ? InstitutePlacement::forInstitute($instituteId) : [];
        $updates = $instituteId !== null ? InstituteUpdate::forInstitute($instituteId) : [];

        $this->view('dashboard/institute/profile', [
            'user' => $sessionUser,
            'institute' => $institute,
            'faculty' => $instituteId !== null ? InstituteFaculty::forInstitute($instituteId) : [],
            'gallery' => $instituteId !== null ? InstituteGallery::forInstitute($instituteId) : [],
            'certificates' => $instituteId !== null ? InstituteCertificate::forInstitute($instituteId) : [],
            'placements' => $placements,
            'profileScore' => $institute !== null ? InstituteProfileScorer::score($institute, $placements, $updates) : null,
            'instituteTypesCatalog' => require base_path('app/data/institute_types.php'),
            'specializationsCatalog' => require base_path('app/data/institute_specializations.php'),
            'facilitiesCatalog' => require base_path('app/data/institute_facilities.php'),
        ], 'employer');
    }

    public function update(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $data = $request->only([
            'name', 'description', 'institute_type', 'established_year', 'website',
            'location', 'city', 'state', 'phone', 'official_email',
            'linkedin_url', 'instagram_url', 'youtube_url', 'facebook_url', 'twitter_url',
        ]);

        $validator = Validator::make($data);
        $validator->validate([
            'name' => 'required|min:2|max:150',
            'website' => 'url',
            'established_year' => 'numeric',
            'official_email' => 'email',
            'linkedin_url' => 'url',
            'instagram_url' => 'url',
            'youtube_url' => 'url',
            'facebook_url' => 'url',
            'twitter_url' => 'url',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/institute');
            return;
        }

        if (($data['established_year'] ?? '') === '') {
            $data['established_year'] = null;
        }

        // Chip multi-selects - not single validated strings, read and
        // comma-join separately (same pattern as the student Profile
        // Builder's interested_roles/domains_of_interest).
        foreach (['specializations', 'facilities', 'training_modes'] as $chipField) {
            $values = (array) $request->input($chipField, []);
            $values = array_values(array_filter(array_map('trim', $values), fn ($v) => $v !== ''));
            $data[$chipField] = !empty($values) ? implode(', ', $values) : null;
        }

        $logoFile = $request->file('logo');
        if ($logoFile !== null && ($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $data['logo_path'] = (new FileUploadService())->upload($logoFile, 'logo');
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('/dashboard/institute');
                return;
            }
        }

        $coverFile = $request->file('cover');
        if ($coverFile !== null && ($coverFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $data['cover_path'] = (new FileUploadService())->upload($coverFile, 'cover');
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('/dashboard/institute');
                return;
            }
        }

        Institute::saveForUser($userId, $data);

        $institute = Institute::findByUserId($userId);
        InstituteRankingScorer::recordEvent((int) $institute['id'], 'profile_updated');

        Session::flash('success', 'Institute profile updated.');
        $this->redirect('/dashboard/institute');
    }
}
