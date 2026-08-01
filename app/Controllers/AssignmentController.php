<?php

namespace App\Controllers;

use App\Models\Institute;
use App\Models\InstituteCourse;
use App\Models\InstituteCourseAssignment;
use App\Models\InstituteAssignmentSubmission;
use App\Models\InstituteEnrollmentRequest;
use App\Services\FileUploadService;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class AssignmentController extends Controller
{
    // ---- Institute side ----

    public function index(Request $request, string $courseId): void
    {
        $course = $this->ownedCourse($courseId);

        if ($course === null) {
            return;
        }

        $this->view('dashboard/institute/course_assignments', [
            'user' => Session::get('_user'),
            'course' => $course,
            'assignments' => InstituteCourseAssignment::forCourse((int) $courseId),
        ], 'employer');
    }

    public function store(Request $request, string $courseId): void
    {
        $course = $this->ownedCourse($courseId);

        if ($course === null) {
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data['course_id'] = (int) $courseId;
        InstituteCourseAssignment::insert($data);

        Session::flash('success', 'Assignment added.');
        $this->redirect('/dashboard/institute/courses/' . $courseId . '/assignments');
    }

    public function update(Request $request, string $id): void
    {
        $assignment = $this->ownedAssignment($id);

        if ($assignment === null) {
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        InstituteCourseAssignment::update((int) $id, $data);

        Session::flash('success', 'Assignment updated.');
        $this->redirect('/dashboard/institute/courses/' . $assignment['course_id'] . '/assignments');
    }

    public function destroy(Request $request, string $id): void
    {
        $assignment = $this->ownedAssignment($id);

        if ($assignment === null) {
            return;
        }

        InstituteCourseAssignment::delete((int) $id);
        Session::flash('success', 'Assignment deleted.');
        $this->redirect('/dashboard/institute/courses/' . $assignment['course_id'] . '/assignments');
    }

    public function submissions(Request $request, string $assignmentId): void
    {
        $assignment = $this->ownedAssignment($assignmentId);

        if ($assignment === null) {
            return;
        }

        $this->view('dashboard/institute/assignment_submissions', [
            'user' => Session::get('_user'),
            'assignment' => $assignment,
            'submissions' => InstituteAssignmentSubmission::forAssignment((int) $assignmentId),
        ], 'employer');
    }

    public function review(Request $request, string $submissionId): void
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);
        $submission = InstituteAssignmentSubmission::find((int) $submissionId);

        if ($submission === null || $institute === null) {
            Session::flash('error', 'That submission could not be found.');
            $this->redirect('/dashboard/institute/courses');
            return;
        }

        $assignment = InstituteCourseAssignment::find((int) $submission['assignment_id']);
        $course = $assignment !== null ? InstituteCourse::find((int) $assignment['course_id']) : null;

        if ($assignment === null || $course === null || (int) $course['institute_id'] !== (int) $institute['id']) {
            Session::flash('error', 'That submission could not be found.');
            $this->redirect('/dashboard/institute/courses');
            return;
        }

        $feedback = trim((string) $request->input('feedback', ''));
        InstituteAssignmentSubmission::markReviewed((int) $submissionId, $feedback);

        Session::flash('success', 'Feedback saved.');
        $this->redirect('/dashboard/institute/assignments/' . $assignment['id'] . '/submissions');
    }

    protected function fields(): array
    {
        return ['type', 'title', 'description', 'due_date'];
    }

    protected function rules(): array
    {
        return [
            'type' => 'required|in:assignment,project',
            'title' => 'required|min:2|max:200',
            'due_date' => 'date',
        ];
    }

    protected function validated(Request $request): ?array
    {
        $data = $request->only($this->fields());

        $validator = Validator::make($data);
        $validator->validate($this->rules());

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/dashboard/institute/courses');
            return null;
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    protected function ownedCourse(string $courseId): ?array
    {
        $sessionUser = Session::get('_user');
        $institute = Institute::findByUserId((int) $sessionUser['id']);
        $course = InstituteCourse::find((int) $courseId);

        if ($course === null || $institute === null || (int) $course['institute_id'] !== (int) $institute['id']) {
            Session::flash('error', 'That course could not be found.');
            $this->redirect('/dashboard/institute/courses');
            return null;
        }

        return $course;
    }

    protected function ownedAssignment(string $id): ?array
    {
        $assignment = InstituteCourseAssignment::find((int) $id);

        if ($assignment === null) {
            Session::flash('error', 'That assignment could not be found.');
            $this->redirect('/dashboard/institute/courses');
            return null;
        }

        $course = $this->ownedCourse((string) $assignment['course_id']);

        return $course === null ? null : $assignment;
    }

    // ---- Student side ----

    public function myLearning(Request $request): void
    {
        $sessionUser = Session::get('_user');

        $this->view('dashboard/student/my_learning', [
            'user' => $sessionUser,
            'enrollments' => InstituteEnrollmentRequest::forStudentEnrollments((int) $sessionUser['id']),
        ], 'student');
    }

    public function courseShow(Request $request, string $courseId): void
    {
        $sessionUser = Session::get('_user');
        $studentId = (int) $sessionUser['id'];

        if (!InstituteEnrollmentRequest::isEnrolled((int) $courseId, $studentId)) {
            Session::flash('error', 'You are not enrolled in that course.');
            $this->redirect('/dashboard/my-learning');
            return;
        }

        $course = InstituteCourse::find((int) $courseId);
        $assignments = InstituteCourseAssignment::forCourse((int) $courseId, $studentId);

        $this->view('dashboard/student/course_show', [
            'user' => $sessionUser,
            'course' => $course,
            'assignments' => $assignments,
            'enrollment' => InstituteEnrollmentRequest::findEnrollment((int) $courseId, $studentId),
        ], 'student');
    }

    public function submit(Request $request, string $courseId, string $assignmentId): void
    {
        $sessionUser = Session::get('_user');
        $studentId = (int) $sessionUser['id'];

        $assignment = InstituteCourseAssignment::find((int) $assignmentId);

        if ($assignment === null || !InstituteEnrollmentRequest::isEnrolled((int) $assignment['course_id'], $studentId)) {
            Session::flash('error', 'You are not enrolled in that course.');
            $this->redirect('/dashboard/my-learning');
            return;
        }

        $text = trim((string) $request->input('submission_text', ''));
        $filePath = null;

        $file = $request->file('submission_file');
        if ($file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $filePath = (new FileUploadService())->upload($file, 'document');
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('/dashboard/my-learning/' . $assignment['course_id']);
                return;
            }
        }

        if ($text === '' && $filePath === null) {
            Session::flash('error', 'Please write a submission or attach a file.');
            $this->redirect('/dashboard/my-learning/' . $assignment['course_id']);
            return;
        }

        InstituteAssignmentSubmission::submit((int) $assignmentId, $studentId, $text !== '' ? $text : null, $filePath);

        Session::flash('success', 'Submission saved.');
        $this->redirect('/dashboard/my-learning/' . $assignment['course_id']);
    }
}
