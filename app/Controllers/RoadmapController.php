<?php

namespace App\Controllers;

use App\Models\Institute;
use App\Models\InstituteCourse;
use App\Models\LearningRoadmap;
use App\Models\RoadmapMilestone;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class RoadmapController extends Controller
{
    // ---- Public ----

    public function index(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = LearningRoadmap::publicListing($page, $perPage);

        $this->view('pages/roadmaps_public', [
            'roadmaps' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'meta' => [
                'title' => 'Roadmaps - Road2Job',
                'description' => 'Step-by-step learning roadmaps shared by training institutes on Road2Job.',
            ],
        ], 'marketing');
    }

    public function show(Request $request, string $id): void
    {
        $roadmap = LearningRoadmap::findWithInstitute((int) $id);

        if ($roadmap === null) {
            Response::abort(404);
            return;
        }

        $this->view('pages/roadmap_show', [
            'roadmap' => $roadmap,
            'milestones' => RoadmapMilestone::forRoadmap((int) $id),
            'meta' => [
                'title' => $roadmap['title'] . ' - Road2Job',
                'description' => 'A learning roadmap by ' . $roadmap['institute_name'] . ' on Road2Job.',
            ],
        ], 'marketing');
    }

    // ---- Institute dashboard ----

    public function manage(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);

        $roadmapIds = [];
        $roadmaps = $institute !== null ? LearningRoadmap::forInstitute((int) $institute['id']) : [];

        foreach ($roadmaps as &$roadmap) {
            $roadmap['milestones'] = RoadmapMilestone::forRoadmap((int) $roadmap['id']);
        }
        unset($roadmap);

        $this->view('dashboard/institute/roadmaps', [
            'user' => $sessionUser,
            'institute' => $institute,
            'roadmaps' => $roadmaps,
            'courses' => $institute !== null ? InstituteCourse::forInstitute((int) $institute['id']) : [],
        ], 'employer');
    }

    public function store(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);

        if ($institute === null) {
            Session::flash('error', 'Please set up your institute profile first.');
            $this->redirect('/dashboard/institute');
            return;
        }

        $data = $request->only(['title', 'description']);
        $validator = Validator::make($data);
        $validator->validate(['title' => 'required|min:2|max:200']);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/institute/roadmaps');
            return;
        }

        $data['institute_id'] = (int) $institute['id'];
        LearningRoadmap::insert($data);

        Session::flash('success', 'Roadmap created.');
        $this->redirect('/dashboard/institute/roadmaps');
    }

    public function destroy(Request $request, string $id): void
    {
        $roadmap = $this->ownedRoadmap($id);

        if ($roadmap !== null) {
            LearningRoadmap::delete((int) $id);
            Session::flash('success', 'Roadmap deleted.');
        }

        $this->redirect('/dashboard/institute/roadmaps');
    }

    public function addMilestone(Request $request, string $roadmapId): void
    {
        $roadmap = $this->ownedRoadmap($roadmapId);

        if ($roadmap === null) {
            return;
        }

        $data = $request->only(['title', 'description', 'course_id']);
        $validator = Validator::make($data);
        $validator->validate(['title' => 'required|min:2|max:200']);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/institute/roadmaps');
            return;
        }

        $data['course_id'] = !empty($data['course_id']) ? (int) $data['course_id'] : null;
        $data['roadmap_id'] = (int) $roadmapId;
        $data['order_index'] = RoadmapMilestone::nextOrderIndex((int) $roadmapId);

        RoadmapMilestone::insert($data);

        Session::flash('success', 'Milestone added.');
        $this->redirect('/dashboard/institute/roadmaps');
    }

    public function deleteMilestone(Request $request, string $id): void
    {
        $milestone = RoadmapMilestone::find((int) $id);

        if ($milestone !== null && $this->ownedRoadmap((string) $milestone['roadmap_id']) !== null) {
            RoadmapMilestone::delete((int) $id);
            Session::flash('success', 'Milestone removed.');
        }

        $this->redirect('/dashboard/institute/roadmaps');
    }

    protected function ownedRoadmap(string $id): ?array
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);
        $roadmap = LearningRoadmap::find((int) $id);

        if ($roadmap === null || $institute === null || (int) $roadmap['institute_id'] !== (int) $institute['id']) {
            Session::flash('error', 'That roadmap could not be found.');
            $this->redirect('/dashboard/institute/roadmaps');
            return null;
        }

        return $roadmap;
    }
}
