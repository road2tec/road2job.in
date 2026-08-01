<?php

namespace App\Controllers;

use App\Models\College;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

/**
 * Generic store/update/destroy for a college's one-to-many sections
 * (campus drives, department stats, alumni). Ownership is checked via
 * the employer's own college_id (looked up from the session user), not
 * a direct user_id column - mirrors InstituteSubResourceController.
 */
abstract class CollegeSubResourceController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function rules(): array;

    abstract protected function fields(): array;

    protected function redirectTarget(): string
    {
        return '/dashboard/college';
    }

    protected function prepareData(Request $request, array $data): array
    {
        return $data;
    }

    protected function handleUpload(Request $request): array
    {
        return [];
    }

    public function store(Request $request): void
    {
        $college = $this->ownedCollege();

        if ($college === null) {
            Session::flash('error', 'Please set up your college profile first.');
            $this->redirect('/dashboard/college');
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data = array_merge($data, $this->handleUpload($request));
        $data['college_id'] = (int) $college['id'];

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

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $this->prepareData($request, $data);
    }

    protected function ownedCollege(): ?array
    {
        $user = Session::get('_user');

        return College::findByUserId((int) $user['id']);
    }

    protected function ownedRecord(Request $request, string $id): ?array
    {
        $college = $this->ownedCollege();
        $modelClass = $this->modelClass();
        $record = $modelClass::find((int) $id);

        if ($record === null || $college === null || (int) $record['college_id'] !== (int) $college['id']) {
            Session::flash('error', 'That entry could not be found.');
            $this->redirect($this->redirectTarget());
            return null;
        }

        return $record;
    }
}
