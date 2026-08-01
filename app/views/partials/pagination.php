<?php
/**
 * Shared admin table pager. Expects $page (current, 1-based), $perPage,
 * and $total (row count) in scope. Page links preserve every other
 * current query-string param (active search/filter selections), so
 * switching pages never resets a filter.
 */
$totalPages = (int) max(1, ceil($total / max(1, $perPage)));
$page = max(1, min($page, $totalPages));
?>
<?php if ($totalPages > 1): ?>
    <nav aria-label="Pagination" class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
        <ul class="pagination pagination-sm mb-0">
            <?php $prevQuery = array_merge($_GET, ['page' => max(1, $page - 1)]); ?>
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= url(current_path() . '?' . http_build_query($prevQuery)) ?>">Previous</a>
            </li>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === 1 || $p === $totalPages || abs($p - $page) <= 2): ?>
                    <?php $pageQuery = array_merge($_GET, ['page' => $p]); ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url(current_path() . '?' . http_build_query($pageQuery)) ?>"><?= $p ?></a>
                    </li>
                <?php elseif (abs($p - $page) === 3): ?>
                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                <?php endif; ?>
            <?php endfor; ?>
            <?php $nextQuery = array_merge($_GET, ['page' => min($totalPages, $page + 1)]); ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= url(current_path() . '?' . http_build_query($nextQuery)) ?>">Next</a>
            </li>
        </ul>
        <div class="small text-muted"><?= (int) $total ?> total</div>
    </nav>
<?php endif; ?>
