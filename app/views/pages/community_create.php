<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <h1 class="fw-bold mb-4">New Post</h1>

        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= url('/community') ?>" data-guard-submit>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="community-create-category">Category</label>
                        <select id="community-create-category" name="category" class="form-select">
                            <option value="discussion">Discussion</option>
                            <option value="question">Question</option>
                            <option value="interview-experience">Interview Experience</option>
                            <option value="success-story">Success Story</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="community-create-title">Title</label>
                        <input id="community-create-title" type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="community-create-tag">Tag (optional)</label>
                        <input id="community-create-tag" type="text" name="tag" class="form-control" placeholder="e.g. Career Discussion, Placements">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="community-create-body">Body</label>
                        <textarea id="community-create-body" name="body" class="form-control" rows="8" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Publish</button>
                </form>
            </div>
        </div>
    </div>
</section>
