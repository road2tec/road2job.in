<?php

namespace App\Controllers;

use App\Models\ResearchItem;
use App\Services\FileUploadService;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class ResearchController extends Controller
{
    protected const TYPES = ['research-paper', 'project', 'publication', 'conference-paper', 'patent'];

    protected function fields(): array
    {
        return ['type', 'title', 'description', 'authors_collaborators', 'publication_date', 'external_reference'];
    }

    protected function rules(): array
    {
        return [
            'type' => 'required|in:' . implode(',', self::TYPES),
            'title' => 'required|min:2|max:200',
            'publication_date' => 'date',
        ];
    }

    public function index(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $this->view('dashboard/student/research', [
            'user' => $sessionUser,
            'items' => ResearchItem::forUser((int) $sessionUser['id']),
        ], 'student');
    }

    public function store(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data = array_merge($data, $this->handleUpload($request));
        $data['user_id'] = (int) $sessionUser['id'];

        ResearchItem::insert($data);

        Session::flash('success', 'Added successfully.');
        $this->redirect('/dashboard/research');
    }

    public function update(Request $request, string $id): void
    {
        $record = $this->ownedRecord($id);

        if ($record === null) {
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data = array_merge($data, $this->handleUpload($request));

        ResearchItem::update((int) $id, $data);

        Session::flash('success', 'Updated successfully.');
        $this->redirect('/dashboard/research');
    }

    public function destroy(Request $request, string $id): void
    {
        $record = $this->ownedRecord($id);

        if ($record !== null) {
            ResearchItem::delete((int) $id);
            Session::flash('success', 'Deleted.');
        }

        $this->redirect('/dashboard/research');
    }

    protected function validated(Request $request): ?array
    {
        $data = $request->only($this->fields());

        $validator = Validator::make($data);
        $validator->validate($this->rules());

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/research');
            return null;
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    protected function handleUpload(Request $request): array
    {
        $file = $request->file('attachment');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        try {
            $path = (new FileUploadService())->upload($file, 'document');
            return ['attachment_path' => $path];
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            return [];
        }
    }

    protected function ownedRecord(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $record = ResearchItem::find((int) $id);

        if ($record === null || (int) $record['user_id'] !== (int) $sessionUser['id']) {
            Session::flash('error', 'That item could not be found.');
            $this->redirect('/dashboard/research');
            return null;
        }

        return $record;
    }
}
