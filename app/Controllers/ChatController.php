<?php

namespace App\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatRequest;
use App\Models\ChatThread;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Core\Controller;
use Core\Request;
use Core\Session;

class ChatController extends Controller
{
    /**
     * Employer-only: sends a chat request to the student behind one of
     * their own job applications. Lives in the role:employer route group,
     * not the shared group, since only employers ever trigger it - mirrors
     * InterviewController::request()'s exact ownership-check shape.
     */
    public function sendRequest(Request $request, string $applicationId): void
    {
        $sessionUser = Session::get('_user');
        $company = Company::findByUserId((int) $sessionUser['id']);
        $application = JobApplication::find((int) $applicationId);

        if ($application === null || $company === null || (int) $application['company_id'] !== (int) $company['id']) {
            Session::flash('error', 'That application could not be found.');
            $this->redirect('/dashboard/applicants');
            return;
        }

        $studentId = (int) $application['student_id'];
        $existingThread = ChatThread::findBetween((int) $sessionUser['id'], $studentId);

        if ($existingThread !== null) {
            Session::flash('success', "You're already connected with this candidate.");
            $this->redirect('/dashboard/chat/' . $existingThread['id']);
            return;
        }

        if (ChatRequest::hasActiveRequest((int) $sessionUser['id'], $studentId)) {
            Session::flash('error', 'You already have a pending chat request with this candidate.');
            $this->redirect('/dashboard/applicants');
            return;
        }

        $message = trim((string) $request->input('message', ''));
        ChatRequest::create((int) $sessionUser['id'], $studentId, (int) $applicationId, $message !== '' ? $message : null);

        $companyName = $company['name'] ?? 'An employer';
        (new NotificationService())->notify(
            $studentId,
            'New chat request',
            substr("{$sessionUser['full_name']} from {$companyName} wants to start a chat with you. Accept to reply.", 0, 255),
            'chat'
        );

        Session::flash('success', 'Chat request sent.');
        $this->redirect('/dashboard/applicants');
    }

    /**
     * Shared inbox: student's incoming requests with accept/decline
     * controls, or employer's own sent requests (read-only) at the same URL.
     */
    public function requestsIndex(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $isStudent = $sessionUser['role_slug'] === 'student';

        $requests = $isStudent
            ? ChatRequest::forStudent((int) $sessionUser['id'])
            : ChatRequest::forEmployer((int) $sessionUser['id']);

        $this->view('dashboard/chat/requests', [
            'user' => $sessionUser,
            'requests' => $requests,
            'isStudent' => $isStudent,
        ], $isStudent ? 'student' : 'employer');
    }

    /**
     * Student-only in practice (employers have nothing to accept/decline);
     * enforced by checking role + ownership inside rather than a dedicated
     * middleware, since this shares a route group with employer-only actions.
     */
    public function respondToRequest(Request $request, string $id): void
    {
        $sessionUser = Session::get('_user');
        $chatRequest = ChatRequest::find((int) $id);

        if ($chatRequest === null || $sessionUser['role_slug'] !== 'student' || (int) $chatRequest['student_id'] !== (int) $sessionUser['id']) {
            Session::flash('error', 'That chat request could not be found.');
            $this->redirect('/dashboard/chat/requests');
            return;
        }

        $status = $request->input('status');

        if (!in_array($status, ['accepted', 'declined'], true) || $chatRequest['status'] !== 'pending') {
            Session::flash('error', 'That request has already been decided.');
            $this->redirect('/dashboard/chat/requests');
            return;
        }

        ChatRequest::update((int) $id, ['status' => $status]);

        $studentName = $sessionUser['full_name'];
        $threadId = null;

        if ($status === 'accepted') {
            $threadId = (int) ChatThread::createFromRequest((int) $id, (int) $chatRequest['employer_user_id'], (int) $sessionUser['id']);
            (new NotificationService())->notify(
                (int) $chatRequest['employer_user_id'],
                'Chat request accepted',
                substr("{$studentName} accepted your chat request. You can now message them.", 0, 255),
                'chat'
            );
        } else {
            (new NotificationService())->notify(
                (int) $chatRequest['employer_user_id'],
                'Chat request declined',
                substr("{$studentName} declined your chat request.", 0, 255),
                'chat'
            );
        }

        Session::flash('success', $status === 'accepted' ? 'Chat request accepted.' : 'Chat request declined.');
        $this->redirect($threadId !== null ? "/dashboard/chat/{$threadId}" : '/dashboard/chat/requests');
    }

    public function inbox(Request $request): void
    {
        $sessionUser = Session::get('_user');
        $isStudent = $sessionUser['role_slug'] === 'student';

        $this->view('dashboard/chat/inbox', [
            'user' => $sessionUser,
            'threads' => ChatThread::forUser((int) $sessionUser['id'], $isStudent ? 'student' : 'employer'),
        ], $isStudent ? 'student' : 'employer');
    }

    public function show(Request $request, string $threadId): void
    {
        $sessionUser = Session::get('_user');
        $thread = $this->ownedThread((int) $threadId, (int) $sessionUser['id']);

        if ($thread === null) {
            return;
        }

        $isStudent = $sessionUser['role_slug'] === 'student';
        $otherPartyId = $isStudent ? (int) $thread['employer_user_id'] : (int) $thread['student_id'];
        $otherParty = User::find($otherPartyId);

        $this->view('dashboard/chat/show', [
            'user' => $sessionUser,
            'thread' => $thread,
            'isStudent' => $isStudent,
            'otherParty' => $otherParty,
            'messages' => ChatMessage::forThread((int) $threadId),
        ], $isStudent ? 'student' : 'employer');
    }

    public function sendMessage(Request $request, string $threadId): void
    {
        $sessionUser = Session::get('_user');
        $thread = $this->ownedThread((int) $threadId, (int) $sessionUser['id'], true);

        if ($thread === null) {
            return;
        }

        if ($thread['status'] === 'suspended') {
            $this->json(['ok' => false, 'message' => 'This conversation has been suspended by an administrator.'], 403);
            return;
        }

        $body = trim((string) $request->input('body', ''));
        $body = $body !== '' ? substr($body, 0, 2000) : null;

        $attachment = null;
        $file = $request->file('attachment');

        if ($file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $path = (new FileUploadService())->upload($file, 'chat_attachment');
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $attachment = [
                    'path' => $path,
                    'original_name' => $file['name'],
                    'mime' => $file['type'] ?? null,
                    'type' => in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? 'image' : 'file',
                ];
            } catch (\RuntimeException $e) {
                $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
                return;
            }
        }

        if ($body === null && $attachment === null) {
            $this->json(['ok' => false, 'message' => 'Write a message or attach a file.'], 422);
            return;
        }

        $messageId = ChatMessage::send((int) $threadId, (int) $sessionUser['id'], $body, $attachment);
        ChatThread::touchLastMessage((int) $threadId);

        $this->json(['ok' => true, 'message' => array_merge(
            ['id' => (int) $messageId, 'sender_id' => (int) $sessionUser['id'], 'body' => $body, 'created_at' => date('Y-m-d H:i:s')],
            $attachment ?? []
        )]);
    }

    public function poll(Request $request, string $threadId): void
    {
        $sessionUser = Session::get('_user');
        $thread = $this->ownedThread((int) $threadId, (int) $sessionUser['id'], true);

        if ($thread === null) {
            return;
        }

        $afterId = (int) $request->input('after', 0);
        $this->json(['messages' => ChatMessage::forThread((int) $threadId, $afterId)]);
    }

    public function markRead(Request $request, string $threadId): void
    {
        $sessionUser = Session::get('_user');
        $thread = $this->ownedThread((int) $threadId, (int) $sessionUser['id'], true);

        if ($thread === null) {
            return;
        }

        $marked = ChatMessage::markThreadRead((int) $threadId, (int) $sessionUser['id']);
        $this->json(['ok' => true, 'marked' => $marked]);
    }

    /**
     * Shared ownership guard - returns the thread row if the current user
     * is genuinely a participant, otherwise flashes/redirects ($asJson=false,
     * for the HTML thread view) or 404s as JSON ($asJson=true, for the
     * AJAX-only send/poll/read endpoints) and returns null either way.
     * Never leaks whether a given thread ID exists to a non-participant.
     */
    protected function ownedThread(int $threadId, int $userId, bool $asJson = false): ?array
    {
        $thread = ChatThread::participants($threadId);

        if ($thread === null || ((int) $thread['employer_user_id'] !== $userId && (int) $thread['student_id'] !== $userId)) {
            if ($asJson) {
                $this->json(['ok' => false, 'message' => 'Conversation not found.'], 404);
            } else {
                Session::flash('error', 'That conversation could not be found.');
                $this->redirect('/dashboard/chat');
            }

            return null;
        }

        return $thread;
    }
}
