<?php
ob_start();
?>
<form method="get" action="<?= url(current_path()) ?>" class="search-panel mt-2">
    <div class="row g-3 align-items-end">
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
            <label class="form-label" for="jobs-public-company">Company</label>
            <div class="input-icon">
                <i class="bi bi-building"></i>
                <input id="jobs-public-company" type="text" name="company" class="form-control" placeholder="Company name" value="<?= e($filters['company']) ?>">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="jobs-public-skills">Skills</label>
            <div class="input-icon">
                <i class="bi bi-code-slash"></i>
                <input id="jobs-public-skills" type="text" name="skills" class="form-control" placeholder="e.g. React" value="<?= e($filters['skills']) ?>">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="jobs-public-min-salary">Min. salary (₹/yr)</label>
            <div class="input-icon">
                <i class="bi bi-cash"></i>
                <input id="jobs-public-min-salary" type="number" min="0" step="10000" name="min_salary" class="form-control" placeholder="e.g. 400000" value="<?= e($filters['min_salary']) ?>">
            </div>
        </div>
        <div class="col-12 col-md-auto ms-md-auto">
            <button type="submit" class="btn btn-primary btn-search w-100">
                <i class="bi bi-search me-1"></i>Search
            </button>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <?php if (!$lockType): ?>
            <?php foreach (['' => 'Any type', 'full_time' => 'Full-time', 'part_time' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Contract', 'remote' => 'Remote'] as $value => $label): ?>
                <button type="submit" name="type" value="<?= $value ?>" class="filter-chip <?= $filters['type'] === $value ? 'active' : '' ?>"><?= $label ?></button>
            <?php endforeach; ?>
            <span class="vr d-none d-sm-block mx-1"></span>
        <?php endif; ?>
        <?php foreach (['' => 'Any experience', 'fresher' => 'Fresher', 'junior' => 'Junior', 'mid' => 'Mid', 'senior' => 'Senior'] as $value => $label): ?>
            <button type="submit" name="experience_level" value="<?= $value ?>" class="filter-chip <?= $filters['experience_level'] === $value ? 'active' : '' ?>"><?= $label ?></button>
        <?php endforeach; ?>
        <button type="submit" name="is_remote" value="<?= $filters['is_remote'] === '1' ? '' : '1' ?>" class="filter-chip <?= $filters['is_remote'] === '1' ? 'active' : '' ?>">
            <i class="bi bi-house-door me-1"></i>Remote only
        </button>
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
    <div class="container" style="max-width: 900px;">
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
                <?php
                $skillTags = array_slice(
                    App\Services\JobMatchScorer::extractSkillsFromText(
                        ($job['title'] ?? '') . ' ' . ($job['description'] ?? '') . ' ' . ($job['requirements'] ?? '')
                    ),
                    0,
                    3
                );
                $isSaved = in_array((int) $job['id'], $savedJobIds, true);
                ?>
                <div class="card listing-card">
                    <div class="card-body">
                        <div class="d-flex gap-3 align-items-start">
                            <a href="<?= url('/jobs/' . $job['id']) ?>" class="text-decoration-none text-reset d-flex gap-3 align-items-start flex-fill">
                                <?php Core\View::partial('partials/company_logo', ['logoPath' => $job['company_logo_path'] ?? null, 'name' => $job['company_name']]); ?>
                                <div class="flex-fill">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h2 class="h6 fw-semibold mb-0 text-reset"><?= e($job['title']) ?></h2>
                                        <?php if ($job['is_remote']): ?>
                                            <span class="badge text-bg-light border">Remote</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted mb-1"><?= e($job['company_name']) ?></div>
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-briefcase me-1"></i><?= e(ucfirst(str_replace('_', ' ', $job['type']))) ?>
                                        <?php if (!empty($job['location'])): ?>
                                            <span class="mx-1">&middot;</span><i class="bi bi-geo-alt me-1"></i><?= e($job['location']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($job['experience_level'])): ?>
                                            <span class="mx-1">&middot;</span><i class="bi bi-bar-chart me-1"></i><?= e(ucfirst($job['experience_level'])) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <?php if (!empty($job['min_salary']) || !empty($job['max_salary'])): ?>
                                            <span class="badge text-bg-light border">
                                                <i class="bi bi-cash me-1"></i><?php
                                                    if (!empty($job['min_salary']) && !empty($job['max_salary'])) {
                                                        echo '₹' . number_format((float) $job['min_salary'] / 100000, 1) . '-' . number_format((float) $job['max_salary'] / 100000, 1) . ' LPA';
                                                    } elseif (!empty($job['max_salary'])) {
                                                        echo 'Up to ₹' . number_format((float) $job['max_salary'] / 100000, 1) . ' LPA';
                                                    } else {
                                                        echo '₹' . number_format((float) $job['min_salary'] / 100000, 1) . '+ LPA';
                                                    }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge text-bg-light border"><i class="bi bi-people me-1"></i><?= (int) $job['applicant_count'] ?> applicant<?= (int) $job['applicant_count'] === 1 ? '' : 's' ?></span>
                                        <?php foreach ($skillTags as $tag): ?>
                                            <span class="badge text-bg-light border"><?= e($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </a>
                            <div class="d-flex flex-column gap-2 flex-shrink-0">
                                <?php if (is_authenticated() && auth()['role_slug'] === 'student'): ?>
                                    <form method="post" action="<?= url('/jobs/' . $job['id'] . '/save') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="<?= $isSaved ? 'Unsave job' : 'Save job' ?>" title="<?= $isSaved ? 'Saved' : 'Save for later' ?>">
                                            <i class="bi bi-bookmark<?= $isSaved ? '-check-fill' : '' ?>"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary js-share-job" data-job-title="<?= e($job['title']) ?>" data-job-url="<?= url('/jobs/' . $job['id']) ?>" aria-label="Share job" title="Share">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>

<script>
document.querySelectorAll('.js-share-job').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var title = btn.dataset.jobTitle;
        var url = btn.dataset.jobUrl;
        if (navigator.share) {
            navigator.share({ title: title, url: url }).catch(function () {});
        } else {
            navigator.clipboard.writeText(url).then(function () {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(function () { btn.innerHTML = original; }, 1500);
            });
        }
    });
});
</script>
