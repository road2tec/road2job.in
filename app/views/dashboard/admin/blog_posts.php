<?php
$statusBadge = ['draft' => 'text-bg-secondary', 'published' => 'text-bg-success'];
?>
<h1 class="h4 mb-1">Blog</h1>
<p class="text-muted mb-4">Write and publish blog posts.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/blog') ?>" class="row g-2">
            <div class="col-md-9">
                <input type="text" name="keyword" class="form-control" placeholder="Search post title or author" value="<?= e($keyword) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text me-2 text-primary"></i>Posts</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#postModal" onclick="resetResourceModal('postModal', '<?= url('/admin/blog') ?>')">New Post</button>
    </div>
    <div class="card-body">
        <?php if (empty($posts)): ?>
            <p class="text-muted small mb-0">No posts yet.</p>
        <?php endif; ?>
        <?php foreach ($posts as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong><?= e($row['title']) ?></strong>
                    <span class="badge <?= $statusBadge[$row['status']] ?? 'text-bg-secondary' ?> ms-1"><?= e(ucfirst($row['status'])) ?></span>
                    <br>
                    <span class="small text-muted">by <?= e($row['author_name']) ?> &middot; <?= e($row['created_at']) ?></span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#postModal"
                        onclick='openResourceModal("postModal", "<?= url('/admin/blog/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/admin/blog/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this post?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>

<!-- Post modal -->
<div class="modal fade" id="postModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Blog post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="blog-posts-title">Title</label><input id="blog-posts-title" type="text" name="title" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="blog-posts-body">Body</label><textarea id="blog-posts-body" name="body" class="form-control" rows="8" required></textarea></div>
                    <div class="mb-2">
                        <label class="form-label" for="blog-posts-status">Status</label>
                        <select id="blog-posts-status" name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/profile.js') ?>"></script>
