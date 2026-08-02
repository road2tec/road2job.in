<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use Core\Controller;
use Core\Request;
use Core\Session;

class AdminChatController extends Controller
{
    public function index(Request $request): void
    {
        $keyword = (string) $request->input('keyword', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $result = ChatThread::adminListing($keyword !== '' ? $keyword : null, $page, $perPage);

        $this->view('dashboard/admin/chats', [
            'user' => Session::get('_user'),
            'threads' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'keyword' => $keyword,
        ], 'admin');
    }

    /**
     * Read-only transcript for dispute review - deliberately no composer,
     * no poll script. Admins reviewing a conversation want a stable
     * snapshot, not one that shifts under them mid-review.
     */
    public function show(Request $request, string $id): void
    {
        $thread = ChatThread::participants((int) $id);

        if ($thread === null) {
            Session::flash('error', 'That conversation could not be found.');
            $this->redirect('/admin/chats');
            return;
        }

        $this->view('dashboard/admin/chat_show', [
            'user' => Session::get('_user'),
            'thread' => $thread,
            'messages' => ChatMessage::forThread((int) $id),
        ], 'admin');
    }

    public function updateStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');
        $thread = ChatThread::participants((int) $id);

        if ($thread === null || !in_array($status, ['active', 'suspended'], true)) {
            Session::flash('error', 'That conversation could not be found.');
            $this->redirect('/admin/chats');
            return;
        }

        $actor = Session::get('_user');
        $reason = $status === 'suspended' ? trim((string) $request->input('reason', '')) : null;

        ChatThread::setStatus((int) $id, $status, (int) $actor['id'], $reason !== '' ? $reason : null);
        AuditLog::record((int) $actor['id'], 'admin_chat_status_update', "Set chat thread #{$id} status to {$status}", $request->ip());

        Session::flash('success', $status === 'suspended' ? 'Conversation suspended.' : 'Conversation reactivated.');
        $this->redirect('/admin/chats');
    }
}
