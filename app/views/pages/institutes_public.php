<?php
ob_start();
?>
<form method="get" action="<?= url(current_path()) ?>" class="search-panel search-panel--compact mt-2">
    <div class="d-flex flex-wrap gap-2">
        <div class="input-icon flex-grow-1" style="min-width: 160px;">
            <i class="bi bi-geo-alt"></i>
            <input type="text" name="city" class="form-control" placeholder="City" aria-label="City" value="<?= e($filters['city']) ?>">
        </div>
        <div class="input-icon flex-grow-1" style="min-width: 160px;">
            <i class="bi bi-easel"></i>
            <input type="text" name="institute_type" class="form-control" placeholder="Type, e.g. Bootcamp" aria-label="Institute type" value="<?= e($filters['institute_type']) ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-search">
            <i class="bi bi-search me-1"></i>Search
        </button>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-2">
        <?php foreach (['' => 'Any mode', 'Online' => 'Online', 'Offline' => 'Offline', 'Hybrid' => 'Hybrid'] as $value => $label): ?>
            <button type="submit" name="training_mode" value="<?= $value ?>" class="filter-chip <?= $filters['training_mode'] === $value ? 'active' : '' ?>"><?= $label ?></button>
        <?php endforeach; ?>
        <span class="vr d-none d-sm-block mx-1"></span>
        <button type="submit" name="verified_only" value="<?= !empty($filters['verified_only']) ? '' : '1' ?>" class="filter-chip <?= !empty($filters['verified_only']) ? 'active' : '' ?>">
            <i class="bi bi-patch-check me-1"></i>Verified only
        </button>
    </div>
</form>
<?php
$searchForm = ob_get_clean();

Core\View::partial('partials/page_header', [
    'title' => 'Training Institutes',
    'subtitle' => 'Browse institutes, their courses and placement records. Ranking reflects recent, genuine activity - not payment.',
    'below' => $searchForm,
    'extraClass' => 'page-header--compact',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($institutes)): ?>
            <?php if (array_filter($filters)): ?>
                <div class="empty-state">
                    <div class="empty-state__icon"><i class="bi bi-easel"></i></div>
                    <h2 class="fw-semibold h5">No institutes match your filters</h2>
                    <p class="text-muted mb-3">Try widening your search - clear a filter or broaden the keyword.</p>
                    <a href="<?= url(current_path()) ?>" class="btn btn-outline-primary btn-sm">Clear filters</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state__icon"><i class="bi bi-easel"></i></div>
                    <h2 class="fw-semibold h5">No institutes listed yet</h2>
                    <p class="text-muted mb-0">Check back soon as training institutes join the platform.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($institutes)): ?>
            <form method="get" action="<?= url('/institutes/compare') ?>" id="compareForm">
                <p class="small text-muted mb-2">Select up to 4 institutes to compare side by side.</p>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($institutes as $institute): ?>
                        <div class="card listing-card">
                            <div class="card-body d-flex gap-3 align-items-start">
                                <div class="form-check pt-1">
                                    <input class="form-check-input compare-checkbox" type="checkbox" name="ids[]" value="<?= (int) $institute['id'] ?>" id="compare-<?= (int) $institute['id'] ?>" aria-label="Select <?= e($institute['name']) ?> for comparison">
                                </div>
                                <?php if (!empty($institute['logo_path'])): ?>
                                    <img src="<?= url($institute['logo_path']) ?>" alt="<?= e($institute['name']) ?>" class="listing-card__logo" loading="lazy">
                                <?php else: ?>
                                    <div class="listing-card__logo-fallback"><i class="bi bi-easel"></i></div>
                                <?php endif; ?>
                                <a href="<?= url('/institutes/' . $institute['id']) ?>" class="text-decoration-none text-reset flex-fill">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h2 class="h6 fw-semibold mb-0"><?= e($institute['name']) ?></h2>
                                        <?php if (($institute['verification_status'] ?? 'unverified') === 'verified'): ?>
                                            <span class="badge text-bg-light border"><i class="bi bi-patch-check-fill text-primary me-1"></i>Verified</span>
                                        <?php endif; ?>
                                        <?php if ((float) ($institute['average_rating'] ?? 0) > 0): ?>
                                            <span class="small fw-semibold"><i class="bi bi-star-fill text-warning me-1"></i><?= number_format((float) $institute['average_rating'], 1) ?><span class="text-muted fw-normal"> (<?= (int) $institute['review_count'] ?>)</span></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <?php $displayLocation = trim(($institute['city'] ?? '') . (!empty($institute['state']) ? ', ' . $institute['state'] : '')) ?: ($institute['location'] ?? ''); ?>
                                        <?php if (!empty($displayLocation)): ?><i class="bi bi-geo-alt me-1"></i><?= e($displayLocation) ?><?php endif; ?>
                                        <?php if (!empty($institute['institute_type'])): ?><span class="mx-1">&middot;</span><?= e($institute['institute_type']) ?><?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge text-bg-light border"><i class="bi bi-mortarboard me-1"></i><?= (int) $institute['course_count'] ?> course<?= (int) $institute['course_count'] === 1 ? '' : 's' ?></span>
                                        <?php if ((int) $institute['placement_count'] > 0): ?>
                                            <span class="badge text-bg-light border"><i class="bi bi-graph-up-arrow me-1"></i><?= (int) $institute['placement_count'] ?> placed</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <i class="bi bi-arrow-right text-muted d-none d-sm-block"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" id="compareSubmitBtn" class="btn btn-outline-primary btn-sm mt-3" disabled>Compare selected</button>
            </form>
        <?php endif; ?>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkboxes = document.querySelectorAll('.compare-checkbox');
    var submitBtn = document.getElementById('compareSubmitBtn');
    if (!submitBtn) return;

    function update() {
        var checked = document.querySelectorAll('.compare-checkbox:checked');
        submitBtn.disabled = checked.length < 2;
        checkboxes.forEach(function (cb) {
            cb.disabled = !cb.checked && checked.length >= 4;
        });
    }

    checkboxes.forEach(function (cb) { cb.addEventListener('change', update); });
});
</script>
