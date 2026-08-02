<?php
/**
 * Premium public institute portfolio (/institutes/{id}). Reuses the same
 * .portfolio-* visual system built for the student portfolio (portfolio.css)
 * rather than inventing a second design language - both are "premium
 * portfolios" on the same site. No section-reorder manager here (unlike
 * the student Portfolio Manager) since the brief's institute ranking/
 * discovery ask never mentions section ordering - sections are just
 * server-side !empty()-gated, in a fixed, sensible order.
 */

$socialFields = [
    'linkedin_url' => ['label' => 'LinkedIn', 'icon' => 'bi-linkedin'],
    'instagram_url' => ['label' => 'Instagram', 'icon' => 'bi-instagram'],
    'youtube_url' => ['label' => 'YouTube', 'icon' => 'bi-youtube'],
    'facebook_url' => ['label' => 'Facebook', 'icon' => 'bi-facebook'],
    'twitter_url' => ['label' => 'Twitter / X', 'icon' => 'bi-twitter-x'],
];
$socialRow = [];
foreach ($socialFields as $field => $meta) {
    if (!empty($institute[$field])) {
        $socialRow[$field] = $meta;
    }
}

$specializations = !empty($institute['specializations']) ? array_map('trim', explode(',', $institute['specializations'])) : [];
$facilities = !empty($institute['facilities']) ? array_map('trim', explode(',', $institute['facilities'])) : [];
$trainingModes = !empty($institute['training_modes']) ? array_map('trim', explode(',', $institute['training_modes'])) : [];

$typeLabels = ['announcement' => 'Announcement', 'placement_news' => 'Placement News', 'achievement' => 'Achievement', 'event' => 'Event', 'course_update' => 'Course Update', 'general' => 'General'];

$yearsActive = !empty($institute['established_year']) ? max(0, (int) date('Y') - (int) $institute['established_year']) : null;
$statsVisible = !empty($courses) || !empty($placements) || $yearsActive !== null || $averageRating !== null;

$sections = [
    'about' => !empty($institute['description']),
    'courses' => !empty($courses),
    'placements' => !empty($placements),
    'updates' => !empty($updates),
    'faculty' => !empty($faculty),
    'facilities' => !empty($specializations) || !empty($facilities) || !empty($trainingModes),
    'gallery' => !empty($gallery),
    'certificates' => !empty($certificates),
    'reviews' => true,
    'contact' => true,
];
$sectionLabels = [
    'about' => 'About', 'courses' => 'Courses', 'placements' => 'Placements', 'updates' => 'Updates',
    'faculty' => 'Faculty', 'facilities' => 'Facilities', 'gallery' => 'Gallery',
    'certificates' => 'Certificates', 'reviews' => 'Reviews', 'contact' => 'Contact',
];
$visibleSections = array_keys(array_filter($sections));
?>
<div class="portfolio-page">

    <?php if (!empty($isOwnInstitute) && ($institute['status'] ?? 'active') !== 'active'): ?>
        <div class="alert alert-warning rounded-0 mb-0 text-center no-print">
            <i class="bi bi-eye-slash me-1"></i>This institute page has been deactivated by an admin - only you can see it right now.
        </div>
    <?php endif; ?>

    <section class="portfolio-hero<?= !empty($institute['cover_path']) ? ' portfolio-hero--has-cover' : '' ?>"<?= !empty($institute['cover_path']) ? ' style="--cover-url: url(\'' . e(url($institute['cover_path'])) . '\')"' : '' ?>>
        <div class="container portfolio-hero__inner text-center text-md-start">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end gap-4">
                <?php if (!empty($institute['logo_path'])): ?>
                    <img src="<?= url($institute['logo_path']) ?>" alt="<?= e($institute['name']) ?>" class="portfolio-hero__avatar" loading="lazy">
                <?php else: ?>
                    <div class="portfolio-hero__avatar-fallback"><i class="bi bi-easel"></i></div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <h1 class="fw-bold mb-1"><?= e($institute['name']) ?></h1>
                    <?php if (!empty($institute['institute_type'])): ?>
                        <p class="fs-6 mb-2" style="color: var(--r2j-gold);"><?= e($institute['institute_type']) ?></p>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start portfolio-hero__meta">
                        <?php $displayLocation = trim(($institute['city'] ?? '') . (!empty($institute['state']) ? ', ' . $institute['state'] : '')) ?: ($institute['location'] ?? ''); ?>
                        <?php if (!empty($displayLocation)): ?>
                            <span><i class="bi bi-geo-alt me-1"></i><?= e($displayLocation) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($institute['established_year'])): ?>
                            <span><i class="bi bi-calendar3 me-1"></i>Est. <?= e($institute['established_year']) ?></span>
                        <?php endif; ?>
                        <?php if (($institute['verification_status'] ?? 'unverified') === 'verified'): ?>
                            <span class="portfolio-availability"><span class="portfolio-availability__dot"></span>Verified institute</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mt-4">
                <?php if (in_array('courses', $visibleSections, true)): ?>
                    <a href="#courses" class="btn btn-lg fw-semibold" style="background: var(--r2j-gradient); color: var(--r2j-primary-fill-text);">View Courses</a>
                <?php endif; ?>
                <?php if (!empty($institute['website'])): ?>
                    <a href="<?= e($institute['website']) ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg">Visit Website</a>
                <?php endif; ?>
                <a href="#contact" class="btn btn-outline-light btn-lg">Contact Institute</a>
            </div>

            <?php if (!empty($socialRow)): ?>
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mt-4 portfolio-social-row">
                    <?php foreach ($socialRow as $field => $meta): ?>
                        <a href="<?= e($institute[$field]) ?>" target="_blank" rel="noopener" aria-label="<?= e($meta['label']) ?>"><i class="bi <?= $meta['icon'] ?>"></i></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($visibleSections)): ?>
        <nav class="portfolio-subnav no-print" aria-label="Institute sections">
            <div class="container">
                <div class="portfolio-subnav__scroll">
                    <?php foreach ($visibleSections as $navKey): ?>
                        <a class="portfolio-subnav__link" href="#<?= e($navKey) ?>"><?= e($sectionLabels[$navKey] ?? $navKey) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <?php if ($statsVisible): ?>
        <section class="py-4 border-bottom">
            <div class="container">
                <div class="row g-4">
                    <div class="col-6 col-md-3 portfolio-stat">
                        <div class="portfolio-stat__value" data-counter="<?= count($courses) ?>">0</div>
                        <div class="portfolio-stat__label">Courses</div>
                    </div>
                    <div class="col-6 col-md-3 portfolio-stat">
                        <div class="portfolio-stat__value" data-counter="<?= count($placements) ?>">0</div>
                        <div class="portfolio-stat__label">Placements</div>
                    </div>
                    <div class="col-6 col-md-3 portfolio-stat">
                        <div class="portfolio-stat__value" data-counter="<?= (int) ($yearsActive ?? 0) ?>">0</div>
                        <div class="portfolio-stat__label">Years Active</div>
                    </div>
                    <div class="col-6 col-md-3 portfolio-stat">
                        <div class="portfolio-stat__value"><?= $averageRating !== null ? e($averageRating) : '-' ?></div>
                        <div class="portfolio-stat__label">Avg. Rating</div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['about']): ?>
        <section id="about" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">Get to know us</div>
                <h2 class="portfolio-section__title mb-4">About</h2>
                <p style="max-width: 48rem;"><?= nl2br(e($institute['description'])) ?></p>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['courses']): ?>
        <section id="courses" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">Learn with us</div>
                <h2 class="portfolio-section__title mb-4">Courses &amp; Programs</h2>
                <div class="row g-4">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="portfolio-project-card">
                                <div class="portfolio-project-card__media"><i class="bi bi-mortarboard"></i></div>
                                <div class="p-3">
                                    <h3 class="h6 fw-bold mb-1"><?= e($course['title']) ?></h3>
                                    <p class="small text-muted mb-2"><?= e(ucfirst($course['mode'])) ?> &middot; <?= e(ucfirst($course['format'])) ?><?= !empty($course['duration']) ? ' &middot; ' . e($course['duration']) : '' ?></p>
                                    <?php if (!empty($course['description'])): ?>
                                        <p class="small mb-2"><?= e(mb_strimwidth($course['description'], 0, 140, '...')) ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-muted"><?= !empty($course['fee']) ? '&#8377;' . number_format((float) $course['fee']) : 'Contact for fee' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['placements']): ?>
        <section id="placements" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">Placement success</div>
                <h2 class="portfolio-section__title mb-2">Placed Students</h2>
                <p class="text-muted small mb-4">Institute-reported and shown as such - not independently verified by Road2Job.</p>

                <form method="get" class="search-panel row g-2 mb-4" style="max-width: 32rem;">
                    <div class="col-8"><input type="text" name="company" class="form-control" placeholder="Search by company..." value="<?= e($placementFilters['company'] ?? '') ?>"></div>
                    <div class="col-4"><button type="submit" class="btn btn-outline-primary w-100">Search</button></div>
                </form>

                <?php $lastYear = null; ?>
                <div class="row g-3">
                    <?php foreach ($placements as $placement): ?>
                        <?php $year = $placement['placement_year'] ?? null; ?>
                        <?php if ($year !== null && $year !== $lastYear): $lastYear = $year; ?>
                            <div class="col-12 portfolio-timeline-year"><?= e($year) ?></div>
                        <?php endif; ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="portfolio-cert-card d-flex gap-3 align-items-start">
                                <?php if (!empty($placement['student_photo_path'])): ?>
                                    <img src="<?= url($placement['student_photo_path']) ?>" alt="" class="rounded-circle flex-shrink-0" style="width:44px;height:44px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="portfolio-cert-card__icon flex-shrink-0"><i class="bi bi-person-check"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= e($placement['student_name']) ?></div>
                                    <div class="small text-muted"><?= e($placement['job_role'] ?? 'Placed') ?> at <?= e($placement['company_name']) ?></div>
                                    <?php if (!empty($placement['package_amount'])): ?><div class="small text-muted">&#8377;<?= number_format((float) $placement['package_amount']) ?>/yr</div><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['updates']): ?>
        <section id="updates" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">Latest from us</div>
                <h2 class="portfolio-section__title mb-4">Institute Updates</h2>
                <div class="row g-3">
                    <?php foreach ($updates as $update): ?>
                        <div class="col-md-6">
                            <div class="portfolio-cert-card">
                                <span class="portfolio-tech-badge mb-2 d-inline-block"><?= e($typeLabels[$update['category']] ?? $update['category']) ?></span>
                                <div class="fw-semibold"><?= e($update['title']) ?></div>
                                <p class="small text-muted mb-1"><?= nl2br(e($update['body'])) ?></p>
                                <div class="small text-muted"><?= e(display_date($update['created_at'] ?? '', '')) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['faculty']): ?>
        <section id="faculty" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">Meet the team</div>
                <h2 class="portfolio-section__title mb-4">Faculty</h2>
                <div class="row g-4">
                    <?php foreach ($faculty as $member): ?>
                        <div class="col-md-6 col-lg-4 d-flex gap-3 align-items-start">
                            <?php if (!empty($member['photo_path'])): ?>
                                <img src="<?= url($member['photo_path']) ?>" alt="<?= e($member['name']) ?>" class="rounded-circle" loading="lazy" style="width:56px;height:56px;object-fit:cover;">
                            <?php else: ?>
                                <div class="portfolio-cert-card__icon flex-shrink-0"><i class="bi bi-person"></i></div>
                            <?php endif; ?>
                            <div>
                                <strong><?= e($member['name']) ?></strong>
                                <?php if (!empty($member['designation'])): ?><div class="small text-muted"><?= e($member['designation']) ?></div><?php endif; ?>
                                <?php if (!empty($member['expertise'])): ?><div class="small text-muted"><?= e($member['expertise']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['facilities']): ?>
        <section id="facilities" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">What we offer</div>
                <h2 class="portfolio-section__title mb-4">Facilities &amp; Specializations</h2>
                <div class="row g-4">
                    <?php if (!empty($specializations)): ?>
                        <div class="col-md-4">
                            <h3 class="h6 fw-bold mb-2">Specializations</h3>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($specializations as $item): ?><span class="portfolio-tech-badge"><?= e($item) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($facilities)): ?>
                        <div class="col-md-4">
                            <h3 class="h6 fw-bold mb-2">Facilities</h3>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($facilities as $item): ?><span class="portfolio-tech-badge"><?= e($item) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($trainingModes)): ?>
                        <div class="col-md-4">
                            <h3 class="h6 fw-bold mb-2">Training Modes</h3>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($trainingModes as $item): ?><span class="portfolio-tech-badge"><?= e($item) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['gallery']): ?>
        <section id="gallery" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">Campus life</div>
                <h2 class="portfolio-section__title mb-4">Gallery</h2>
                <div class="row g-2">
                    <?php foreach ($gallery as $image): ?>
                        <div class="col-6 col-md-3">
                            <img src="<?= url($image['image_path']) ?>" class="rounded w-100" loading="lazy" style="height:140px;object-fit:cover;" alt="<?= e($image['caption'] ?? '') ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sections['certificates']): ?>
        <section id="certificates" class="portfolio-section">
            <div class="container">
                <div class="portfolio-section__eyebrow">Recognized &amp; accredited</div>
                <h2 class="portfolio-section__title mb-4">Accreditation &amp; Certificates</h2>
                <div class="row g-3">
                    <?php foreach ($certificates as $certificate): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="portfolio-cert-card">
                                <div class="portfolio-cert-card__icon mb-3"><i class="bi bi-award"></i></div>
                                <div class="fw-semibold"><?= e($certificate['title']) ?></div>
                                <div class="small text-muted"><?= e($certificate['issuing_body'] ?? '') ?><?= !empty($certificate['issued_year']) ? ' &middot; ' . e($certificate['issued_year']) : '' ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section id="reviews" class="portfolio-section">
        <div class="container">
            <div class="portfolio-section__eyebrow">Student voices</div>
            <h2 class="portfolio-section__title mb-4">Reviews</h2>

            <?php if (empty($reviews)): ?>
                <p class="text-muted small">No reviews yet.</p>
            <?php endif; ?>
            <div class="row g-3 mb-4">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-md-6">
                        <div class="portfolio-cert-card">
                            <div class="d-flex justify-content-between">
                                <strong><?= e($review['reviewer_name']) ?></strong>
                                <span class="small text-warning"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></span>
                            </div>
                            <?php if (!empty($review['review_text'])): ?><p class="small text-muted mb-0 mt-1"><?= nl2br(e($review['review_text'])) ?></p><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($isStudent && !$alreadyReviewed && !$isOwnInstitute): ?>
                <h3 class="h6 fw-semibold mb-2">Write a review</h3>
                <form method="post" action="<?= url('/institutes/' . $institute['id'] . '/reviews') ?>" data-guard-submit style="max-width: 32rem;">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label" for="institute-show-rating">Rating</label>
                        <select id="institute-show-rating" name="rating" class="form-select" style="max-width: 160px;" required>
                            <option value="">Select</option>
                            <?php foreach ([5, 4, 3, 2, 1] as $star): ?>
                                <option value="<?= $star ?>"><?= $star ?> star<?= $star === 1 ? '' : 's' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="institute-show-review-text">Review (optional)</label>
                        <textarea id="institute-show-review-text" name="review_text" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Submit review</button>
                </form>
            <?php elseif (!$isStudent && !$alreadyReviewed): ?>
                <p class="text-muted small mb-0"><a href="<?= url('/login') ?>">Log in as a student</a> to write a review.</p>
            <?php endif; ?>
        </div>
    </section>

    <section id="contact" class="portfolio-section">
        <div class="container">
            <div class="portfolio-section__eyebrow">Get in touch</div>
            <h2 class="portfolio-section__title mb-4">Contact</h2>
            <div class="row g-3">
                <?php if (!empty($institute['official_email'])): ?>
                    <div class="col-md-6">
                        <a href="mailto:<?= e($institute['official_email']) ?>" class="portfolio-contact-item">
                            <span class="portfolio-contact-item__icon"><i class="bi bi-envelope"></i></span>
                            <span><?= e($institute['official_email']) ?></span>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($institute['phone'])): ?>
                    <div class="col-md-6">
                        <a href="tel:<?= e($institute['phone']) ?>" class="portfolio-contact-item">
                            <span class="portfolio-contact-item__icon"><i class="bi bi-telephone"></i></span>
                            <span><?= e($institute['phone']) ?></span>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($institute['website'])): ?>
                    <div class="col-md-6">
                        <a href="<?= e($institute['website']) ?>" target="_blank" rel="noopener" class="portfolio-contact-item">
                            <span class="portfolio-contact-item__icon"><i class="bi bi-globe2"></i></span>
                            <span><?= e($institute['website']) ?></span>
                        </a>
                    </div>
                <?php endif; ?>
                <?php foreach ($socialRow as $field => $meta): ?>
                    <div class="col-md-6">
                        <a href="<?= e($institute[$field]) ?>" target="_blank" rel="noopener" class="portfolio-contact-item">
                            <span class="portfolio-contact-item__icon"><i class="bi <?= $meta['icon'] ?>"></i></span>
                            <span><?= e($meta['label']) ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="portfolio-footer no-print">
        <div class="container">
            <div class="fw-bold fs-5 mb-1"><?= e($institute['name']) ?></div>
            <?php if (!empty($institute['institute_type'])): ?><div class="small mb-3" style="opacity:.8;"><?= e($institute['institute_type']) ?></div><?php endif; ?>
            <div class="small" style="opacity:.6;">Built with <a href="<?= url('/') ?>">Road2Job</a> &middot; &copy; <?= date('Y') ?></div>
            <p class="small mt-3"><a href="<?= url('/institutes') ?>" style="opacity:.8;">&larr; Back to all institutes</a></p>
        </div>
    </footer>
</div>
