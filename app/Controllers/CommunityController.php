<?php

namespace App\Controllers;

use App\Models\CommunityPost;
use App\Models\CommunityReply;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class CommunityController extends Controller
{
    protected const CATEGORIES = ['discussion', 'question', 'interview-experience', 'success-story'];

    public function index(Request $request): void
    {
        $category = (string) $request->input('category', '');
        $category = in_array($category, self::CATEGORIES, true) ? $category : null;
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 24;
        $result = CommunityPost::forCategory($category, 50, $page, $perPage);

        $this->view('pages/community_index', [
            'posts' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'activeCategory' => $category,
            'categories' => self::CATEGORIES,
            'meta' => [
                'title' => 'Community - Road2Job',
                'description' => 'Discussions, questions, interview experiences and success stories from the Road2Job community.',
            ],
        ], 'marketing');
    }

    public function create(Request $request): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in to start a discussion.');
            $this->redirect('/login');
            return;
        }

        $this->view('pages/community_create', [
            'categories' => self::CATEGORIES,
            'meta' => [
                'title' => 'New Post - Road2Job Community',
                'description' => 'Start a new discussion, question, interview experience, or success story.',
            ],
        ], 'marketing');
    }

    public function store(Request $request): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in to post.');
            $this->redirect('/login');
            return;
        }

        $category = (string) $request->input('category', 'discussion');
        if (!in_array($category, self::CATEGORIES, true)) {
            $category = 'discussion';
        }

        $title = trim((string) $request->input('title', ''));
        $body = trim((string) $request->input('body', ''));
        $tag = trim((string) $request->input('tag', ''));

        if ($title === '' || $body === '') {
            Session::flash('error', 'Please fill in a title and body.');
            $this->redirect('/community/new');
            return;
        }

        $postId = CommunityPost::insert([
            'user_id' => (int) $sessionUser['id'],
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'tag' => $tag !== '' ? $tag : null,
        ]);

        Session::flash('success', 'Post published.');
        $this->redirect('/community/' . $postId);
    }

    public function show(Request $request, string $id): void
    {
        $post = CommunityPost::findWithAuthor((int) $id);

        if ($post === null) {
            Response::abort(404);
            return;
        }

        $sessionUser = Session::get('_user');
        $viewerId = $sessionUser !== null ? (int) $sessionUser['id'] : null;

        CommunityPost::incrementViews((int) $id, $viewerId);

        $this->view('pages/community_show', [
            'post' => $post,
            'replies' => CommunityReply::forPost((int) $id),
            'isAuthor' => $viewerId !== null && $viewerId === (int) $post['user_id'],
            'isLoggedIn' => $sessionUser !== null,
            'meta' => [
                'title' => $post['title'] . ' - Road2Job Community',
                'description' => mb_substr(strip_tags($post['body']), 0, 150),
            ],
        ], 'marketing');
    }

    public function reply(Request $request, string $postId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            Session::flash('error', 'Please log in to reply.');
            $this->redirect('/login');
            return;
        }

        $post = CommunityPost::find((int) $postId);

        if ($post === null) {
            Response::abort(404);
            return;
        }

        $body = trim((string) $request->input('body', ''));

        if ($body === '') {
            Session::flash('error', 'Reply cannot be empty.');
            $this->redirect('/community/' . $postId);
            return;
        }

        CommunityReply::createForPost((int) $postId, (int) $sessionUser['id'], $body);

        Session::flash('success', 'Reply posted.');
        $this->redirect('/community/' . $postId);
    }

    public function acceptReply(Request $request, string $postId, string $replyId): void
    {
        $sessionUser = Session::get('_user');

        if ($sessionUser === null) {
            $this->redirect('/login');
            return;
        }

        $post = CommunityPost::find((int) $postId);

        if ($post === null || (int) $post['user_id'] !== (int) $sessionUser['id']) {
            Session::flash('error', 'Only the post author can accept an answer.');
            $this->redirect('/community/' . $postId);
            return;
        }

        $reply = CommunityReply::find((int) $replyId);

        if ($reply === null || (int) $reply['community_post_id'] !== (int) $postId) {
            Session::flash('error', 'That reply could not be found.');
            $this->redirect('/community/' . $postId);
            return;
        }

        CommunityPost::markAcceptedReply((int) $postId, (int) $replyId);

        Session::flash('success', 'Answer marked as accepted.');
        $this->redirect('/community/' . $postId);
    }
}
