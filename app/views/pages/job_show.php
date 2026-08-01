<?php
$employmentTypeMap = ['full_time' => 'FULL_TIME', 'part_time' => 'PART_TIME', 'internship' => 'INTERN', 'contract' => 'CONTRACTOR', 'remote' => 'OTHER'];

$jobLd = [
    '@context' => 'https://schema.org/',
    '@type' => 'JobPosting',
    'title' => $job['title'],
    'description' => $job['description'] ?? $job['title'],
    'datePosted' => !empty($job['published_at']) ? date('Y-m-d', strtotime($job['published_at'])) : date('Y-m-d', strtotime($job['created_at'])),
    'employmentType' => $employmentTypeMap[$job['type']] ?? 'OTHER',
    'hiringOrganization' => [
        '@type' => 'Organization',
        'name' => $job['company_name'],
    ],
];

if (!empty($job['is_remote'])) {
    $jobLd['jobLocationType'] = 'TELECOMMUTE';
    $jobLd['applicantLocationRequirements'] = ['@type' => 'Country', 'name' => 'IN'];
}

if (!empty($job['location'])) {
    $jobLd['jobLocation'] = [
        '@type' => 'Place',
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job['location'], 'addressCountry' => 'IN'],
    ];
}

if (!empty($job['min_salary']) || !empty($job['max_salary'])) {
    $jobLd['baseSalary'] = [
        '@type' => 'MonetaryAmount',
        'currency' => 'INR',
        'value' => [
            '@type' => 'QuantitativeValue',
            'minValue' => (float) ($job['min_salary'] ?? $job['max_salary']),
            'maxValue' => (float) ($job['max_salary'] ?? $job['min_salary']),
            'unitText' => 'YEAR',
        ],
    ];
}
?>
<script type="application/ld+json"><?= json_encode($jobLd, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-start mb-4">
                    <?php if (!empty($job['company_logo_path'])): ?>
                        <img src="<?= url($job['company_logo_path']) ?>" alt="<?= e($job['company_name']) ?>" class="rounded border" loading="lazy" style="width:64px;height:64px;object-fit:cover;">
                    <?php else: ?>
                        <div class="feature-icon" style="width:64px;height:64px;"><i class="bi bi-building"></i></div>
                    <?php endif; ?>
                    <div>
                        <h1 class="h4 fw-bold mb-1"><?= e($job['title']) ?></h1>
                        <div class="text-muted"><?= e($job['company_name']) ?><?= !empty($job['company_location']) ? ' &middot; ' . e($job['company_location']) : '' ?></div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge text-bg-light border p-2"><i class="bi bi-briefcase me-1"></i><?= e(ucfirst(str_replace('_', ' ', $job['type']))) ?></span>
                    <span class="badge text-bg-light border p-2"><i class="bi bi-bar-chart me-1"></i><?= e(ucfirst($job['experience_level'])) ?></span>
                    <?php if (!empty($job['location'])): ?><span class="badge text-bg-light border p-2"><i class="bi bi-geo-alt me-1"></i><?= e($job['location']) ?></span><?php endif; ?>
                    <?php if ($job['is_remote']): ?><span class="badge text-bg-light border p-2"><i class="bi bi-wifi me-1"></i>Remote friendly</span><?php endif; ?>
                    <?php if (!empty($job['min_salary']) || !empty($job['max_salary'])): ?>
                        <span class="badge text-bg-light border p-2"><i class="bi bi-cash-stack me-1"></i>₹<?= number_format((float) $job['min_salary']) ?> - ₹<?= number_format((float) $job['max_salary']) ?>/yr</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($job['description'])): ?>
                    <h2 class="h6 fw-semibold">Description</h2>
                    <p class="mb-4"><?= nl2br(e($job['description'])) ?></p>
                <?php endif; ?>

                <?php if (!empty($job['requirements'])): ?>
                    <h2 class="h6 fw-semibold">Requirements</h2>
                    <p class="mb-4"><?= nl2br(e($job['requirements'])) ?></p>
                <?php endif; ?>

                <?php if (!empty($job['application_deadline'])): ?>
                    <p class="text-muted small">Apply before <?= e($job['application_deadline']) ?></p>
                <?php endif; ?>

                <?php if ($isStudent): ?>
                    <div class="border-top pt-4 mt-4">
                        <h2 class="h6 fw-semibold mb-2"><i class="bi bi-graph-up-arrow me-1 text-primary"></i>Your Match</h2>
                        <?php if ($matchScore !== null && $matchScore['percent'] !== null): ?>
                            <p class="mb-2"><span class="badge text-bg-<?= $matchScore['percent'] >= 70 ? 'success' : ($matchScore['percent'] >= 40 ? 'warning' : 'secondary') ?> p-2"><?= (int) $matchScore['percent'] ?>% match</span></p>
                            <?php if (!empty($matchScore['matched'])): ?>
                                <p class="small mb-1 text-muted">You have:
                                    <?php foreach ($matchScore['matched'] as $skill): ?><span class="badge text-bg-light border me-1"><?= e($skill) ?></span><?php endforeach; ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($matchScore['missing'])): ?>
                                <p class="small mb-0 text-muted">Consider adding:
                                    <?php foreach ($matchScore['missing'] as $skill): ?><span class="badge text-bg-light border me-1"><?= e($skill) ?></span><?php endforeach; ?>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="small text-muted mb-0">Add skills to your <a href="<?= url('/dashboard/profile') ?>">profile</a> to see how well you match this role.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="border-top pt-4 mt-4">
                    <?php if ($isStudent): ?>
                        <?php if ($hasApplied): ?>
                            <span class="badge text-bg-success p-2 mb-2"><i class="bi bi-check-circle me-1"></i>Applied</span>
                        <?php else: ?>
                            <form method="post" action="<?= url('/jobs/' . $job['id'] . '/apply') ?>" class="mb-3" data-guard-submit>
                                <?= csrf_field() ?>
                                <div class="mb-2">
                                    <label class="form-label small" for="job-show-cover-note">Cover note (optional)</label>
                                    <textarea id="job-show-cover-note" name="cover_note" class="form-control" rows="2" placeholder="A short note to the employer..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Apply now</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= url('/jobs/' . $job['id'] . '/save') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-bookmark<?= $isSaved ? '-check-fill' : '' ?> me-1"></i><?= $isSaved ? 'Saved' : 'Save for later' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="btn btn-primary">Log in to apply</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <p class="text-center mt-3"><a href="<?= url('/jobs') ?>">&larr; Back to all jobs</a></p>
    </div>
</section>
