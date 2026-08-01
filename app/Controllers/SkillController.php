<?php

namespace App\Controllers;

use App\Models\StudentSkill;
use Core\Database;
use Core\Request;
use Core\Session;

class SkillController extends StudentSubResourceController
{
    protected function modelClass(): string
    {
        return StudentSkill::class;
    }

    protected function fields(): array
    {
        return ['skill_name', 'proficiency'];
    }

    protected function rules(): array
    {
        return [
            'skill_name' => 'required|max:100',
            'proficiency' => 'required|in:beginner,intermediate,advanced,expert',
        ];
    }

    /**
     * Adds any number of skills in one submit (the profile builder's chip
     * UI collects them client-side into parallel skills[]/proficiencies[]
     * arrays) - one transaction, one redirect, instead of N separate
     * store() round trips. Case-insensitive dedupe against both the
     * student's existing skills and duplicates within the same batch
     * ("Java"/"java"/"JAVA" must never become 3 rows). Invalid/blank/
     * duplicate entries are silently skipped rather than failing the
     * whole batch - this is a convenience bulk-add, not a strict form.
     */
    public function bulkStore(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $userId = (int) $sessionUser['id'];

        $names = (array) $request->input('skills', []);
        $proficiencies = (array) $request->input('proficiencies', []);
        $allowedProficiencies = ['beginner', 'intermediate', 'advanced', 'expert'];

        $existing = StudentSkill::existingNamesLower($userId);
        $seenInBatch = [];
        $toInsert = [];
        $submittedCount = 0;

        foreach (array_values($names) as $i => $rawName) {
            $name = trim((string) $rawName);
            if ($name === '') {
                continue;
            }
            $submittedCount++;

            if (mb_strlen($name) > 100) {
                continue;
            }

            $lower = mb_strtolower($name);
            if (isset($existing[$lower]) || isset($seenInBatch[$lower])) {
                continue;
            }

            $proficiency = $proficiencies[$i] ?? 'intermediate';
            if (!in_array($proficiency, $allowedProficiencies, true)) {
                $proficiency = 'intermediate';
            }

            $seenInBatch[$lower] = true;
            $toInsert[] = ['skill_name' => $name, 'proficiency' => $proficiency];
        }

        if (empty($toInsert)) {
            Session::flash('error', 'No new skills to add - they may already be on your profile.');
            $this->redirect('/dashboard/profile');
            return;
        }

        $db = Database::connection();
        $db->beginTransaction();

        try {
            foreach ($toInsert as $row) {
                StudentSkill::insert(array_merge($row, ['user_id' => $userId]));
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('error', 'Could not save skills - please try again.');
            $this->redirect('/dashboard/profile');
            return;
        }

        $addedCount = count($toInsert);
        $skippedCount = $submittedCount - $addedCount;

        $message = $addedCount . ' skill' . ($addedCount === 1 ? '' : 's') . ' added.';
        if ($skippedCount > 0) {
            $message .= ' ' . $skippedCount . ' skipped (already on your profile or invalid).';
        }

        Session::flash('success', $message);
        $this->redirect('/dashboard/profile');
    }
}
