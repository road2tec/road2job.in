<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\BlogPost;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class BlogController extends Controller
{
    // ---- Public ----

    public function index(Request $request): void
    {
        $this->view('pages/blog_index', [
            'posts' => BlogPost::publishedListing(),
            'meta' => [
                'title' => 'Blog - Road2Job',
                'description' => 'Career advice, interview prep, and placement insights from the Road2Job team.',
            ],
        ], 'marketing');
    }

    public function show(Request $request, string $id): void
    {
        $post = BlogPost::findPublished((int) $id);

        if ($post === null) {
            Response::abort(404);
            return;
        }

        $this->view('pages/blog_show', [
            'post' => $post,
            'meta' => [
                'title' => $post['title'] . ' - Road2Job Blog',
                'description' => mb_substr(strip_tags($post['body']), 0, 160),
            ],
        ], 'marketing');
    }

    // ---- Admin authoring ----

    public function adminIndex(Request $request): void
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $result = BlogPost::adminListing($keyword, $page, $perPage);

        $this->view('dashboard/admin/blog_posts', [
            'user' => Session::get('_user'),
            'posts' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'keyword' => $keyword,
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        $data['author_id'] = (int) Session::get('_user')['id'];

        if ($data['status'] === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $id = BlogPost::insert($data);

        AuditLog::record($data['author_id'], 'admin_blog_create', "Created blog post #{$id}: {$data['title']}", $request->ip());

        Session::flash('success', 'Blog post created.');
        $this->redirect('/admin/blog');
    }

    public function update(Request $request, string $id): void
    {
        $post = BlogPost::find((int) $id);

        if ($post === null) {
            Session::flash('error', 'That post could not be found.');
            $this->redirect('/admin/blog');
            return;
        }

        $data = $this->validated($request);

        if ($data === null) {
            return;
        }

        if ($data['status'] === 'published' && $post['published_at'] === null) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        BlogPost::update((int) $id, $data);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_blog_update', "Updated blog post #{$id}", $request->ip());

        Session::flash('success', 'Blog post updated.');
        $this->redirect('/admin/blog');
    }

    public function destroy(Request $request, string $id): void
    {
        BlogPost::delete((int) $id);

        $actor = Session::get('_user');
        AuditLog::record((int) $actor['id'], 'admin_blog_delete', "Deleted blog post #{$id}", $request->ip());

        Session::flash('success', 'Blog post deleted.');
        $this->redirect('/admin/blog');
    }

    protected function validated(Request $request): ?array
    {
        $data = $request->only(['title', 'body', 'status']);

        $validator = Validator::make($data);
        $validator->validate([
            'title' => 'required|min:2|max:200',
            'body' => 'required',
            'status' => 'required|in:draft,published',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/admin/blog');
            return null;
        }

        return $data;
    }
}
