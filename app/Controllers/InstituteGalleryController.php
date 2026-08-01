<?php

namespace App\Controllers;

use App\Models\InstituteGallery;
use App\Services\FileUploadService;
use Core\Request;
use Core\Session;

class InstituteGalleryController extends InstituteSubResourceController
{
    protected function modelClass(): string
    {
        return InstituteGallery::class;
    }

    protected function fields(): array
    {
        return ['caption'];
    }

    protected function rules(): array
    {
        return [];
    }

    /**
     * image_path is required on create (a gallery entry with no image
     * makes no sense) - self-contained so a missing/invalid file always
     * gets a friendly flash instead of ever reaching a NOT NULL SQL error.
     * Does not delegate to the parent's store(), since that would call
     * handleUpload() a second time against an already-consumed tmp file.
     */
    public function store(Request $request): void
    {
        $institute = $this->ownedInstitute();

        if ($institute === null) {
            Session::flash('error', 'Please set up your institute profile first.');
            $this->redirect('/dashboard/institute');
            return;
        }

        $file = $request->file('image');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            Session::flash('error', 'Please choose an image to upload.');
            $this->redirect($this->redirectTarget());
            return;
        }

        try {
            $imagePath = (new FileUploadService())->upload($file, 'gallery');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect($this->redirectTarget());
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data['image_path'] = $imagePath;
        $data['institute_id'] = (int) $institute['id'];

        InstituteGallery::insert($data);

        Session::flash('success', 'Added successfully.');
        $this->redirect($this->redirectTarget());
    }
}
