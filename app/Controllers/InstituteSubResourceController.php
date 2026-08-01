<?php

namespace App\Controllers;

use App\Models\Institute;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

/**
 * Generic store/update/destroy for an institute's one-to-many sections
 * (courses, faculty, gallery, certificates, placements). Ownership is
 * checked via the employer's own institute_id (looked up from the
 * session user), not a direct user_id column on the resource itself -
 * same indirect-ownership shape Phase 6's JobPostingController needed.
 */
abstract class InstituteSubResourceController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function rules(): array;

    abstract protected function fields(): array;

    protected function redirectTarget(): string
    {
        return '/dashboard/institute';
    }

    protected function prepareData(Request $request, array $data): array
    {
        return $data;
    }

    /**
     * Hook for subclasses that accept a file upload (Faculty/Certificate).
     * Mirrors StudentSubResourceController's contract exactly: catches its
     * own RuntimeException, flashes, and returns [] on failure rather than
     * aborting the whole save.
     */
    protected function handleUpload(Request $request): array
    {
        return [];
    }

    /**
     * No-op by default - InstitutePlacementController/InstituteUpdateController
     * override this to trigger InstituteRankingScorer::recompute() after a
     * save/delete, since those two resource types feed the ranking formula
     * and must reflect changes immediately rather than waiting for the
     * lazy 24h staleness sweep. Courses/faculty/gallery/certificates don't
     * override it and are unaffected.
     */
    protected function afterSave(array $institute): void
    {
        // Intentionally empty.
    }

    public function store(Request $request): void
    {
        $institute = $this->ownedInstitute();

        if ($institute === null) {
            Session::flash('error', 'Please set up your institute profile first.');
            $this->redirect('/dashboard/institute');
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data = array_merge($data, $this->handleUpload($request));
        $data['institute_id'] = (int) $institute['id'];

        $modelClass = $this->modelClass();
        $modelClass::insert($data);
        $this->afterSave($institute);

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
        $this->afterSave($this->ownedInstitute());

        Session::flash('success', 'Updated successfully.');
        $this->redirect($this->redirectTarget());
    }

    public function destroy(Request $request, string $id): void
    {
        $record = $this->ownedRecord($request, $id);

        if ($record !== null) {
            $modelClass = $this->modelClass();
            $modelClass::delete((int) $id);
            $this->afterSave($this->ownedInstitute());
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

    protected function ownedInstitute(): ?array
    {
        $user = Session::get('_user');

        return Institute::findByUserId((int) $user['id']);
    }

    protected function ownedRecord(Request $request, string $id): ?array
    {
        $institute = $this->ownedInstitute();
        $modelClass = $this->modelClass();
        $record = $modelClass::find((int) $id);

        if ($record === null || $institute === null || (int) $record['institute_id'] !== (int) $institute['id']) {
            Session::flash('error', 'That entry could not be found.');
            $this->redirect($this->redirectTarget());
            return null;
        }

        return $record;
    }
}
