<?php
Core\View::partial('partials/page_header', [
    'title' => 'Compare Institutes',
    'subtitle' => 'Factual, side-by-side information only - nothing fabricated. A field shows "Not provided" when the institute hasn\'t added it.',
]);

$fmt = function ($value): string {
    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
        return '<span class="text-muted">Not provided</span>';
    }
    if (is_array($value)) {
        $value = implode(', ', $value);
    }
    return e((string) $value);
};
?>

<section class="py-5">
    <div class="container">
        <?php if (count($institutes) < 2): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-easel"></i></div>
                <h2 class="fw-semibold h5">Select at least 2 institutes to compare</h2>
                <p class="text-muted mb-3">Go back to the directory and choose up to 4 institutes.</p>
                <a href="<?= url('/institutes') ?>" class="btn btn-outline-primary btn-sm">Browse institutes</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 160px;">&nbsp;</th>
                            <?php foreach ($institutes as $institute): ?>
                                <th style="min-width: 220px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($institute['logo_path'])): ?>
                                            <img src="<?= url($institute['logo_path']) ?>" alt="" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                        <?php else: ?>
                                            <div class="listing-card__logo-fallback" style="width:40px;height:40px;"><i class="bi bi-easel"></i></div>
                                        <?php endif; ?>
                                        <a href="<?= url('/institutes/' . $institute['id']) ?>" class="fw-semibold text-decoration-none"><?= e($institute['name']) ?></a>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="small text-muted">Verified</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= ($institute['verification_status'] ?? 'unverified') === 'verified' ? '<span class="badge text-bg-success">Verified</span>' : '<span class="text-muted">Not verified</span>' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Location</th>
                            <?php foreach ($institutes as $institute): ?>
                                <?php $loc = trim(($institute['city'] ?? '') . (!empty($institute['state']) ? ', ' . $institute['state'] : '')) ?: ($institute['location'] ?? null); ?>
                                <td><?= $fmt($loc) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Established</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= $fmt($institute['established_year'] ?? null) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Type</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= $fmt($institute['institute_type'] ?? null) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Training modes</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= $fmt(!empty($institute['training_modes']) ? array_map('trim', explode(',', $institute['training_modes'])) : null) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Specializations</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= $fmt(!empty($institute['specializations']) ? array_map('trim', explode(',', $institute['specializations'])) : null) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Facilities</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= $fmt(!empty($institute['facilities']) ? array_map('trim', explode(',', $institute['facilities'])) : null) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Published courses</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= (int) $institute['course_count'] ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Placements on record</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= (int) $institute['placement_count'] ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Avg. package</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= $institute['average_package'] !== null ? '&#8377;' . number_format($institute['average_package']) . '/yr' : '<span class="text-muted">Not provided</span>' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Avg. review rating</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= $institute['average_rating'] !== null ? e($institute['average_rating']) . '/5' : '<span class="text-muted">Not provided</span>' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Profile completeness</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= (int) ($institute['profile_completion_percent'] ?? 0) ?>%</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="small text-muted">Website</th>
                            <?php foreach ($institutes as $institute): ?>
                                <td><?= !empty($institute['website']) ? '<a href="' . e($institute['website']) . '" target="_blank" rel="noopener" class="small">Visit</a>' : '<span class="text-muted">Not provided</span>' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-3">Ranking and placement figures are institute-reported and reflect Road2Job's activity-based ranking - not an independent audit.</p>
            <p><a href="<?= url('/institutes') ?>">&larr; Back to all institutes</a></p>
        <?php endif; ?>
    </div>
</section>
