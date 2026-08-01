<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

/**
 * Generic store/update/destroy for a student's one-to-many profile
 * sections (education, skills, projects, etc). Concrete controllers just
 * declare their model class, validation rules and form fields - this
 * base class carries the shared ownership-checked CRUD + flash/redirect
 * flow so it isn't repeated seven times.
 */
abstract class StudentSubResourceController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function rules(): array;

    abstract protected function fields(): array;

    protected function redirectTarget(): string
    {
        return '/dashboard/profile';
    }

    /**
     * Hook for subclasses to post-process validated data before saving
     * (e.g. casting a checkbox, nulling a field based on another field).
     */
    protected function prepareData(Request $request, array $data): array
    {
        return $data;
    }

    /**
     * Hook for subclasses that accept a file upload (Project/Certificate).
     * Returns extra fields to merge into the saved row.
     */
    protected function handleUpload(Request $request): array
    {
        return [];
    }

    public function store(Request $request): void
    {
        $user = Session::get('_user');
        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data = array_merge($data, $this->handleUpload($request));
        $data['user_id'] = (int) $user['id'];

        $modelClass = $this->modelClass();
        $modelClass::insert($data);

        Session::flash('success', 'Added successfully.');
        $this->redirect($this->redirectTarget());
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

        $data = array_merge($data, $this->handleUpload($request));

        $modelClass = $this->modelClass();
        $modelClass::update((int) $id, $data);

        Session::flash('success', 'Updated successfully.');
        $this->redirect($this->redirectTarget());
    }

    public function destroy(Request $request, string $id): void
    {
        $record = $this->ownedRecord($request, $id);

        if ($record !== null) {
            $modelClass = $this->modelClass();
            $modelClass::delete((int) $id);
            Session::flash('success', 'Deleted.');
        }

        $this->redirect($this->redirectTarget());
    }

    protected function validated(Request $request): ?array
    {
        $data = $request->only($this->fields());

        $validator = Validator::make($data);
        $validator->validate($this->rules());

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect($this->redirectTarget());
            return null;
        }

        // Any field still blank here was optional (required fields would
        // already have failed validation above) - store NULL rather than
        // '', since nullable DATE/ENUM columns reject empty strings.
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $this->prepareData($request, $data);
    }

    protected function ownedRecord(Request $request, string $id): ?array
    {
        $user = Session::get('_user');
        $modelClass = $this->modelClass();
        $record = $modelClass::find((int) $id);

        if ($record === null || (int) $record['user_id'] !== (int) $user['id']) {
            Session::flash('error', 'That entry could not be found.');
            $this->redirect($this->redirectTarget());
            return null;
        }

        return $record;
    }
}
