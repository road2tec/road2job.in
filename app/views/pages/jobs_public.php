<?php
ob_start();
?>
<form method="get" action="<?= url(current_path()) ?>" class="search-panel mt-2">
    <div class="row g-3 align-items-end">
        <?php if (!$lockType): ?>
            <div class="col-6 col-md-2">
                <label class="form-label" for="jobs-public-type">Type</label>
                <select id="jobs-public-type" name="type" class="form-select">
                    <option value="">Any</option>
                    <?php foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Contract', 'remote' => 'Remote'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-md-3">
            <label class="form-label" for="jobs-public-keyword">Keyword</label>
            <div class="input-icon">
                <i class="bi bi-search"></i>
                <input id="jobs-public-keyword" type="text" name="keyword" class="form-control" placeholder="Job title..." value="<?= e($filters['keyword']) ?>">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="jobs-public-location">Location</label>
            <div class="input-icon">
                <i class="bi bi-geo-alt"></i>
                <input id="jobs-public-location" type="text" name="location" class="form-control" placeholder="City" value="<?= e($filters['location']) ?>">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="jobs-public-experience-level">Experience</label>
            <select id="jobs-public-experience-level" name="experience_level" class="form-select">
                <option value="">Any</option>
                <?php foreach (['fresher' => 'Fresher', 'junior' => 'Junior', 'mid' => 'Mid', 'senior' => 'Senior'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $filters['experience_level'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-auto d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" name="is_remote" value="1" class="form-check-input" id="filterRemote" <?= $filters['is_remote'] === '1' ? 'checked' : '' ?>>
                <label class="form-check-label small" for="filterRemote">Remote only</label>
            </div>
        </div>
        <div class="col-6 col-md-auto ms-md-auto">
            <button type="submit" class="btn btn-primary btn-search w-100">
                <i class="bi bi-search me-1"></i>Search
            </button>
        </div>
    </div>
</form>
<?php
$searchForm = ob_get_clean();

Core\View::partial('partials/page_header', [
    'title' => $pageTitle,
    'subtitle' => $pageIntro,
    'below' => $searchForm,
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($jobs)): ?>
            <?php if (array_filter($filters)): ?>
                <div class="empty-state">
                    <div class="empty-state__icon"><i class="bi bi-briefcase"></i></div>
                    <h2 class="fw-semibold h5">No jobs match your filters</h2>
                    <p class="text-muted mb-3">Try widening your search - clear a filter or broaden the keyword.</p>
                    <a href="<?= url(current_path()) ?>" class="btn btn-outline-primary btn-sm">Clear filters</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state__icon"><i class="bi bi-briefcase"></i></div>
                    <h2 class="fw-semibold h5">No jobs posted yet</h2>
                    <p class="text-muted mb-0">Check back soon as employers start hiring.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($jobs as $job): ?>
                <a href="<?= url('/jobs/' . $job['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body d-flex gap-3 align-items-start">
                        <?php if (!empty($job['company_logo_path'])): ?>
                            <img src="<?= url($job['company_logo_path']) ?>" alt="<?= e($job['company_name']) ?>" class="listing-card__logo" loading="lazy">
                        <?php else: ?>
                            <div class="listing-card__logo-fallback"><i class="bi bi-building"></i></div>
                        <?php endif; ?>
                        <div class="flex-fill">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h2 class="h6 fw-semibold mb-0"><?= e($job['title']) ?></h2>
                                <?php if ($job['is_remote']): ?>
                                    <span class="badge text-bg-light border">Remote</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted mb-1"><?= e($job['company_name']) ?></div>
                            <div class="small text-muted">
                                <i class="bi bi-briefcase me-1"></i><?= e(ucfirst(str_replace('_', ' ', $job['type']))) ?>
                                <?php if (!empty($job['location'])): ?>
                                    <span class="mx-1">&middot;</span><i class="bi bi-geo-alt me-1"></i><?= e($job['location']) ?>
                                <?php endif; ?>
                                <?php if (!empty($job['experience_level'])): ?>
                                    <span class="mx-1">&middot;</span><i class="bi bi-bar-chart me-1"></i><?= e(ucfirst($job['experience_level'])) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="bi bi-arrow-right text-muted d-none d-sm-block"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
