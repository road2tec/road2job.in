<?php
$typeLabels = [
    'research-paper' => 'Research Paper',
    'project' => 'Research Project',
    'publication' => 'Publication',
    'conference-paper' => 'Conference Paper',
    'patent' => 'Patent',
];

ob_start();
?>
<div class="mt-3 d-flex gap-2 flex-wrap">
    <a href="<?= url('/research-hub') ?>" class="filter-chip <?= $activeType === null ? 'active' : '' ?>">All</a>
    <?php foreach ($types as $type): ?>
        <a href="<?= url('/research-hub?type=' . $type) ?>" class="filter-chip <?= $activeType === $type ? 'active' : '' ?>"><?= e($typeLabels[$type]) ?></a>
    <?php endforeach; ?>
</div>
<?php
$chips = ob_get_clean();

Core\View::partial('partials/page_header', [
    'title' => 'Research Hub',
    'subtitle' => 'Research papers, projects, publications, conference papers and patents shared by students.',
    'below' => $chips,
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 840px;">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-journal-bookmark"></i></div>
                <h2 class="fw-semibold h5">No research items shared yet</h2>
                <p class="text-muted mb-0">Students haven't published anything in this category yet - check back soon or browse another type.</p>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($items as $item): ?>
                <a href="<?= url('/research-hub/' . $item['id']) ?>" class="card listing-card text-decoration-none text-reset">
                    <div class="card-body">
                        <span class="badge text-bg-light border mb-2"><?= e($typeLabels[$item['type']] ?? $item['type']) ?></span>
                        <h2 class="h6 fw-semibold mb-1"><?= e($item['title']) ?></h2>
                        <div class="small text-muted">
                            by <?= e($item['author_name']) ?>
                            <?php if (!empty($item['publication_date'])): ?> &middot; <?= e($item['publication_date']) ?><?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php Core\View::partial('partials/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total]); ?>
    </div>
</section>
