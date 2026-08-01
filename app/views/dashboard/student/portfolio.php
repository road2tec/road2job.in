<h1 class="h4 mb-1">Portfolio</h1>
<p class="text-muted mb-4">Your public, shareable profile page.</p>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-globe me-2 text-primary"></i>Your public link</div>
            <div class="card-body">
                <?php $visibility = $profile['profile_visibility'] ?? 'private'; ?>
                <div class="mb-3">
                    <?php if ($visibility === 'public'): ?>
                        <span class="badge text-bg-success">Public</span>
                        <span class="text-muted small ms-1">Anyone with the link can view this.</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Private</span>
                        <span class="text-muted small ms-1">Only you can view this right now.</span>
                    <?php endif; ?>
                </div>

                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="portfolioUrl" value="<?= e($portfolioUrl) ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyUrlBtn">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                    <a href="<?= e($portfolioUrl) ?>" target="_blank" class="btn btn-primary">
                        <i class="bi bi-box-arrow-up-right"></i> View
                    </a>
                </div>
                <p class="text-muted small mb-0">Publish/unpublish and section layout are below. Change your username from <a href="<?= url('/dashboard/settings') ?>">Settings</a>.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>Views</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h3 fw-bold mb-0"><?= (int) $totalViews ?></div>
                        <div class="small text-muted">Total views</div>
                    </div>
                    <div class="col-6">
                        <div class="h3 fw-bold mb-0"><?= (int) $uniqueVisitors ?></div>
                        <div class="small text-muted">Unique visitors</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-qr-code me-2 text-primary"></i>QR Code</div>
            <div class="card-body text-center">
                <div id="portfolioQr" class="d-inline-block mb-3"></div>
                <p class="text-muted small mb-0">Scan to open your portfolio on another device, or share it on printed materials.</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><i class="bi bi-sliders me-2 text-primary"></i>Portfolio Manager</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/portfolio/manage') ?>" id="portfolioManagerForm">
            <?= csrf_field() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label" for="portfolio-visibility">Portfolio status</label>
                    <select id="portfolio-visibility" name="profile_visibility" class="form-select">
                        <option value="public" <?= $visibility === 'public' ? 'selected' : '' ?>>Published (anyone with the link can view)</option>
                        <option value="private" <?= $visibility === 'private' ? 'selected' : '' ?>>Unpublished (only you can view)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="portfolio-theme">Theme</label>
                    <select id="portfolio-theme" name="portfolio_theme" class="form-select">
                        <option value="modern" selected>Modern</option>
                        <option value="developer" disabled>Developer (coming soon)</option>
                        <option value="minimal" disabled>Minimal (coming soon)</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label d-block">Sections</label>
                <p class="text-muted small">Toggle a section off to hide it from your public portfolio, or reorder with the arrows. Sections with no data are always hidden automatically, even if switched on here.</p>
                <ul class="list-group" id="sectionOrderList">
                    <?php foreach ($sectionOrder as $key): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" data-section-key="<?= e($key) ?>">
                            <span class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" checked id="section-<?= e($key) ?>">
                                <label class="form-check-label" for="section-<?= e($key) ?>"><?= e($sections[$key] ?? $key) ?></label>
                            </span>
                            <span class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary section-move-up" aria-label="Move up"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-outline-secondary section-move-down" aria-label="Move down"><i class="bi bi-arrow-down"></i></button>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    <?php foreach (array_diff(array_keys($sections), $sectionOrder) as $key): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" data-section-key="<?= e($key) ?>">
                            <span class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="section-<?= e($key) ?>">
                                <label class="form-check-label" for="section-<?= e($key) ?>"><?= e($sections[$key] ?? $key) ?></label>
                            </span>
                            <span class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary section-move-up" aria-label="Move up"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-outline-secondary section-move-down" aria-label="Move down"><i class="bi bi-arrow-down"></i></button>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div id="sectionOrderInputs"></div>
            </div>

            <div class="mb-4">
                <label class="form-label d-block">Featured projects</label>
                <?php if (empty($projects)): ?>
                    <p class="text-muted small mb-0">Add projects in your <a href="<?= url('/dashboard/profile') ?>">Profile</a> first, then feature your best ones here.</p>
                <?php else: ?>
                    <p class="text-muted small">Featured projects are shown first and stand out visually on your portfolio.</p>
                    <?php foreach ($projects as $project): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="featured_projects[]" value="<?= (int) $project['id'] ?>" id="featured-<?= (int) $project['id'] ?>" <?= !empty($project['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="featured-<?= (int) $project['id'] ?>"><?= e($project['title']) ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Save portfolio settings</button>
        </form>
    </div>
</div>

<script src="<?= asset('js/vendor/qrcode.min.js') ?>"></script>
<script>
    new QRCode(document.getElementById('portfolioQr'), {
        text: <?= json_encode($portfolioUrl) ?>,
        width: 180,
        height: 180,
        correctLevel: QRCode.CorrectLevel.M,
    });

    document.getElementById('copyUrlBtn').addEventListener('click', function () {
        const input = document.getElementById('portfolioUrl');
        input.select();
        navigator.clipboard.writeText(input.value).then(function () {
            const btn = document.getElementById('copyUrlBtn');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
            setTimeout(function () { btn.innerHTML = original; }, 1500);
        });
    });

    (function () {
        var list = document.getElementById('sectionOrderList');
        if (!list) return;

        list.addEventListener('click', function (e) {
            var item = e.target.closest('li[data-section-key]');
            if (!item) return;

            if (e.target.closest('.section-move-up')) {
                var prev = item.previousElementSibling;
                if (prev) list.insertBefore(item, prev);
            } else if (e.target.closest('.section-move-down')) {
                var next = item.nextElementSibling;
                if (next) list.insertBefore(next, item);
            }
        });

        document.getElementById('portfolioManagerForm').addEventListener('submit', function () {
            var inputsBox = document.getElementById('sectionOrderInputs');
            inputsBox.innerHTML = '';
            list.querySelectorAll('li[data-section-key]').forEach(function (li) {
                var checkbox = li.querySelector('.form-check-input');
                if (!checkbox || !checkbox.checked) return;
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'section_order[]';
                hidden.value = li.getAttribute('data-section-key');
                inputsBox.appendChild(hidden);
            });
        });
    })();
</script>
