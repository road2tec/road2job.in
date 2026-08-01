<?php

namespace App\Controllers;

use App\Models\InstituteUpdate;
use App\Services\FileUploadService;
use App\Services\InstituteRankingScorer;
use Core\Request;
use Core\Session;

class InstituteUpdateController extends InstituteSubResourceController
{
    public function index(Request $request): void
    {
        $institute = $this->ownedInstitute();

        $this->view('dashboard/institute/updates', [
            'user' => Session::get('_user'),
            'institute' => $institute,
            'updates' => $institute !== null ? InstituteUpdate::forInstitute((int) $institute['id']) : [],
        ], 'employer');
    }

    protected function modelClass(): string
    {
        return InstituteUpdate::class;
    }

    protected function redirectTarget(): string
    {
        return '/dashboard/institute/updates';
    }

    protected function fields(): array
    {
        return ['category', 'title', 'body'];
    }

    protected function rules(): array
    {
        return [
            'category' => 'required|in:announcement,placement_news,achievement,event,course_update,general',
            'title' => 'required|min:3|max:200',
            'body' => 'required|min:10',
        ];
    }

    protected function prepareData(Request $request, array $data): array
    {
        // Duplicate-post detection input for InstituteRankingScorer - a
        // normalized hash of title+body, so re-posting the same update
        // (even with trivial whitespace differences) is excluded from the
        // ranking sum beyond its first occurrence, not just down-weighted.
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', ($data['title'] ?? '') . '|' . ($data['body'] ?? ''))));
        $data['content_hash'] = hash('sha256', $normalized);

        return $data;
    }

    protected function handleUpload(Request $request): array
    {
        $file = $request->file('image');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        try {
            $path = (new FileUploadService())->upload($file, 'gallery');
            return ['image_path' => $path];
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            return [];
        }
    }

    protected function afterSave(array $institute): void
    {
        InstituteRankingScorer::recordEvent((int) $institute['id'], 'update_posted');
    }
}
