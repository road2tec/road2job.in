<?php
/**
 * Premium public student portfolio (/u/{username}). One source of truth:
 * every value here comes straight from the same Profile Builder data that
 * feeds the resume - nothing is entered twice. Sections are rendered by
 * looping $visibleSections (set in PortfolioController::show(), driven by
 * the student's Portfolio Manager choices) so hide/reorder actually
 * changes this page, not just a CSS toggle.
 */

$fullName = $account['full_name'] ?? '';
$initialsText = initials($fullName);

$interestedRoles = !empty($profile['interested_roles']) ? array_map('trim', explode(',', $profile['interested_roles'])) : [];
$preferredLocations = !empty($profile['preferred_locations']) ? array_map('trim', explode(',', $profile['preferred_locations'])) : [];
$domainsOfInterest = !empty($profile['domains_of_interest']) ? array_map('trim', explode(',', $profile['domains_of_interest'])) : [];
$workTypes = !empty($profile['work_type']) ? array_map('trim', explode(',', $profile['work_type'])) : [];
$availabilityLabels = [
    'actively_looking' => 'Actively looking',
    'open_to_opportunities' => 'Open to opportunities',
    'not_looking' => 'Not looking currently',
];

$socialFields = [
    'linkedin_url' => ['label' => 'LinkedIn', 'icon' => 'bi-linkedin'],
    'github_url' => ['label' => 'GitHub', 'icon' => 'bi-github'],
    'leetcode_url' => ['label' => 'LeetCode', 'icon' => 'bi-code-square'],
    'hackerrank_url' => ['label' => 'HackerRank', 'icon' => 'bi-terminal'],
    'codechef_url' => ['label' => 'CodeChef', 'icon' => 'bi-cup-hot'],
    'behance_url' => ['label' => 'Behance', 'icon' => 'bi-behance'],
    'dribbble_url' => ['label' => 'Dribbble', 'icon' => 'bi-dribbble'],
    'youtube_url' => ['label' => 'YouTube', 'icon' => 'bi-youtube'],
    'website_url' => ['label' => 'Website', 'icon' => 'bi-globe2'],
];
$socialRow = [];
foreach ($socialFields as $field => $socialMeta) {
    if (!empty($profile[$field])) {
        $socialRow[$field] = $socialMeta;
    }
}

$levelMap = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];
$typeLabels = ['research-paper' => 'Research Paper', 'project' => 'Research Project', 'publication' => 'Publication', 'conference-paper' => 'Conference Paper', 'patent' => 'Patent'];
$statsVisible = !empty($skills) || !empty($projects) || !empty($certificates) || !empty($experience);
?>
<div class="portfolio-page">

    <?php if (!empty($isOwner) && empty($isPublicView)): ?>
        <div class="alert alert-warning rounded-0 mb-0 text-center no-print">
            <i class="bi bi-eye-slash me-1"></i>This portfolio is private - only you can see it right now.
            <a href="<?= url('/dashboard/portfolio') ?>">Publish it</a> from the Portfolio Manager.
        </div>
    <?php endif; ?>

    <section class="portfolio-hero">
        <div class="container portfolio-hero__inner text-center text-md-start">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end gap-4">
                <?php if (!empty($profile['avatar_path'])): ?>
                    <img src="<?= url($profile['avatar_path']) ?>" alt="<?= e($fullName) ?>" class="portfolio-hero__avatar" loading="lazy">
                <?php else: ?>
                    <div class="portfolio-hero__avatar-fallback"><?= e($initialsText) ?></div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <h1 class="fw-bold mb-1"><?= e($fullName) ?></h1>
                    <?php if (!empty($profile['headline'])): ?>
                        <p class="fs-5 mb-2" style="color: rgba(255,255,255,.85);"><?= e($profile['headline']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($interestedRoles)): ?>
                        <p class="portfolio-hero__typed mb-2" data-roles='<?= json_encode($interestedRoles, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'></p>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start portfolio-hero__meta">
                        <?php if (!empty($profile['city'])): ?>
                            <span><i class="bi bi-geo-alt me-1"></i><?= e($profile['city']) ?><?= !empty($profile['state']) ? ', ' . e($profile['state']) : '' ?></span>
                        <?php endif; ?>
                        <?php if (!empty($profile['availability'])): ?>
                            <span class="portfolio-availability"><span class="portfolio-availability__dot"></span><?= e($availabilityLabels[$profile['availability']] ?? '') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($profile['career_objective'])): ?>
                <?php $introShort = mb_strlen($profile['career_objective']) > 220 ? mb_substr($profile['career_objective'], 0, 220) . '…' : $profile['career_objective']; ?>
                <p class="portfolio-hero__intro mt-4 mx-auto mx-md-0"><?= nl2br(e($introShort)) ?></p>
            <?php endif; ?>

            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mt-4">
                <?php if (in_array('projects', $visibleSections, true)): ?>
                    <a href="#projects" class="btn btn-lg fw-semibold" style="background: var(--r2j-gradient); color: var(--r2j-primary-fill-text);">View Projects</a>
                <?php endif; ?>
                <a href="<?= e($resumeUrl) ?>" class="btn btn-outline-light btn-lg" target="_blank">Download Resume</a>
                <a href="#contact" class="btn btn-outline-light btn-lg">Contact</a>
            </div>

            <?php if (!empty($socialRow)): ?>
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mt-4 portfolio-social-row">
                    <?php foreach ($socialRow as $field => $socialMeta): ?>
                        <a href="<?= e($profile[$field]) ?>" target="_blank" rel="noopener" aria-label="<?= e($socialMeta['label']) ?>"><i class="bi <?= $socialMeta['icon'] ?>"></i></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($visibleSections)): ?>
        <nav class="portfolio-subnav no-print" aria-label="Portfolio sections">
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
                        <div class="portfolio-stat__value" data-counter="<?= count($projects) ?>">0</div>
                        <div class="portfolio-stat__label">Projects</div>
                    </div>
                    <div class="col-6 col-md-3 portfolio-stat">
                        <div class="portfolio-stat__value" data-counter="<?= count($skills) ?>">0</div>
                        <div class="portfolio-stat__label">Skills</div>
                    </div>
                    <div class="col-6 col-md-3 portfolio-stat">
                        <div class="portfolio-stat__value" data-counter="<?= count($certificates) ?>">0</div>
                        <div class="portfolio-stat__label">Certificates</div>
                    </div>
                    <div class="col-6 col-md-3 portfolio-stat">
                        <div class="portfolio-stat__value" data-counter="<?= count($experience) ?>">0</div>
                        <div class="portfolio-stat__label">Experience</div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php foreach ($visibleSections as $sectionKey): ?>
        <?php switch ($sectionKey):

            case 'about': ?>
                <section id="about" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Get to know me</div>
                        <h2 class="portfolio-section__title mb-4">About</h2>
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <p><?= nl2br(e($profile['career_objective'] ?? '')) ?></p>
                            </div>
                            <div class="col-lg-5">
                                <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0">
                                    <?php if (!empty($education)): $latestEdu = $education[0]; ?>
                                        <li><i class="bi bi-mortarboard me-2 text-primary"></i><?= e($latestEdu['degree']) ?><?= !empty($latestEdu['field_of_study']) ? ' - ' . e($latestEdu['field_of_study']) : '' ?>, <?= e($latestEdu['institution_name']) ?></li>
                                    <?php endif; ?>
                                    <?php if (!empty($profile['city'])): ?>
                                        <li><i class="bi bi-geo-alt me-2 text-primary"></i><?= e($profile['city']) ?><?= !empty($profile['state']) ? ', ' . e($profile['state']) : '' ?></li>
                                    <?php endif; ?>
                                    <?php if (!empty($languages)): ?>
                                        <li><i class="bi bi-translate me-2 text-primary"></i><?= e(implode(', ', array_column($languages, 'language_name'))) ?></li>
                                    <?php endif; ?>
                                    <?php if (!empty($domainsOfInterest)): ?>
                                        <li><i class="bi bi-compass me-2 text-primary"></i><?= e(implode(', ', $domainsOfInterest)) ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'skills': ?>
                <section id="skills" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">What I bring</div>
                        <h2 class="portfolio-section__title mb-4">Skills</h2>
                        <div class="row g-4">
                            <?php foreach ($skillGroups as $groupName => $groupSkills): ?>
                                <div class="col-md-6 col-lg-4 portfolio-skill-group">
                                    <h3><?= e($groupName) ?></h3>
                                    <?php foreach ($groupSkills as $skillRow): $level = $levelMap[$skillRow['proficiency']] ?? 1; ?>
                                        <div class="portfolio-skill">
                                            <div class="portfolio-skill__row">
                                                <span class="portfolio-skill__name"><?= e($skillRow['skill_name']) ?></span>
                                                <span class="portfolio-skill__level"><?= e(ucfirst($skillRow['proficiency'])) ?></span>
                                            </div>
                                            <div class="portfolio-skill__bar"><div class="portfolio-skill__bar-fill" style="width: <?= (int) round($level / 4 * 100) ?>%;"></div></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'projects': ?>
                <section id="projects" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Selected work</div>
                        <h2 class="portfolio-section__title mb-4">Projects</h2>
                        <div class="row g-4">
                            <?php foreach ($projects as $project): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="portfolio-project-card position-relative<?= !empty($project['is_featured']) ? ' portfolio-project-card--featured' : '' ?>">
                                        <?php if (!empty($project['is_featured'])): ?><span class="portfolio-featured-badge">Featured</span><?php endif; ?>
                                        <div class="portfolio-project-card__media"><i class="bi bi-kanban"></i></div>
                                        <div class="p-3">
                                            <h3 class="h6 fw-bold mb-1"><?= e($project['title']) ?></h3>
                                            <?php if (!empty($project['role']) || !empty($project['project_type'])): ?>
                                                <p class="small text-muted mb-2"><?= e($project['role'] ?? '') ?><?= !empty($project['role']) && !empty($project['project_type']) ? ' &middot; ' : '' ?><?= e($project['project_type'] ?? '') ?></p>
                                            <?php endif; ?>
                                            <?php $projectLines = resume_bullet_lines($project['description'] ?? ''); ?>
                                            <?php if (count($projectLines) > 1): ?>
                                                <ul class="small mb-2 ps-3">
                                                    <?php foreach ($projectLines as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="small mb-2"><?= e($project['description'] ?? '') ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($project['technologies_used'])): ?>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <?php foreach (array_map('trim', explode(',', $project['technologies_used'])) as $tech): ?>
                                                        <span class="portfolio-tech-badge"><?= e($tech) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="small text-muted"><?= e(resume_date_range($project['start_date'], $project['end_date'] ?? null, empty($project['end_date']))) ?></span>
                                                <?php if (!empty($project['project_url'])): ?>
                                                    <a href="<?= e($project['project_url']) ?>" target="_blank" rel="noopener" class="small fw-semibold">View <i class="bi bi-box-arrow-up-right"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'experience': ?>
                <section id="experience" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Where I've worked</div>
                        <h2 class="portfolio-section__title mb-4">Experience</h2>
                        <ul class="timeline">
                            <?php $lastYear = null; ?>
                            <?php foreach ($experience as $row): ?>
                                <?php $year = (!empty($row['start_date']) && (int) substr($row['start_date'], 0, 4) >= 1) ? date('Y', strtotime($row['start_date'])) : null; ?>
                                <?php if ($year !== null && $year !== $lastYear): $lastYear = $year; ?>
                                    <li class="portfolio-timeline-year"><?= e($year) ?></li>
                                <?php endif; ?>
                                <li class="timeline-item">
                                    <div class="fw-semibold"><?= e($row['job_title']) ?> &middot; <?= e($row['company_name']) ?></div>
                                    <div class="small text-muted mb-1">
                                        <?= e(ucfirst(str_replace('_', ' ', $row['employment_type']))) ?><?= !empty($row['location']) ? ' &middot; ' . e($row['location']) : '' ?>
                                        &middot; <?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, (bool) $row['currently_working'])) ?>
                                    </div>
                                    <?php $expLines = resume_bullet_lines($row['description'] ?? ''); ?>
                                    <?php if (!empty($expLines)): ?>
                                        <ul class="small mb-1 ps-3">
                                            <?php foreach ($expLines as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($row['skills_used'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (array_map('trim', explode(',', $row['skills_used'])) as $tech): ?>
                                                <span class="portfolio-tech-badge"><?= e($tech) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php break;

            case 'education': ?>
                <section id="education" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">My academic path</div>
                        <h2 class="portfolio-section__title mb-4">Education</h2>
                        <ul class="timeline">
                            <?php $lastEduYear = null; ?>
                            <?php foreach ($education as $row): ?>
                                <?php $eduYear = (!empty($row['start_date']) && (int) substr($row['start_date'], 0, 4) >= 1) ? date('Y', strtotime($row['start_date'])) : null; ?>
                                <?php if ($eduYear !== null && $eduYear !== $lastEduYear): $lastEduYear = $eduYear; ?>
                                    <li class="portfolio-timeline-year"><?= e($eduYear) ?></li>
                                <?php endif; ?>
                                <li class="timeline-item">
                                    <div class="fw-semibold"><?= e($row['degree']) ?><?= !empty($row['field_of_study']) ? ' - ' . e($row['field_of_study']) : '' ?></div>
                                    <div class="small text-muted mb-1">
                                        <?= e($row['institution_name']) ?> &middot; <?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, empty($row['end_date']))) ?>
                                        <?= !empty($row['grade']) ? ' &middot; ' . e($row['grade']) : '' ?>
                                    </div>
                                    <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php break;

            case 'certificates': ?>
                <section id="certificates" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Verified learning</div>
                        <h2 class="portfolio-section__title mb-4">Certificates</h2>
                        <div class="row g-3">
                            <?php foreach ($certificates as $cert): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="portfolio-cert-card">
                                        <div class="portfolio-cert-card__icon mb-3"><i class="bi bi-award"></i></div>
                                        <div class="fw-semibold"><?= e($cert['title']) ?></div>
                                        <?php $issued = resume_month_year($cert['issue_date']); ?>
                                        <div class="small text-muted mb-2"><?= e($cert['issuing_organization']) ?><?= $issued !== '' ? ' &middot; ' . e($issued) : '' ?></div>
                                        <?php if (!empty($cert['credential_url'])): ?>
                                            <a href="<?= e($cert['credential_url']) ?>" target="_blank" rel="noopener" class="small fw-semibold">Verify credential <i class="bi bi-box-arrow-up-right"></i></a>
                                        <?php elseif (!empty($cert['attachment_path'])): ?>
                                            <a href="<?= url($cert['attachment_path']) ?>" target="_blank" rel="noopener" class="small fw-semibold">View certificate <i class="bi bi-box-arrow-up-right"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'achievements': ?>
                <section id="achievements" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Recognition</div>
                        <h2 class="portfolio-section__title mb-4">Achievements</h2>
                        <div class="row g-3">
                            <?php foreach ($achievements as $row): ?>
                                <div class="col-md-6">
                                    <div class="d-flex gap-3">
                                        <div class="portfolio-cert-card__icon flex-shrink-0"><i class="bi bi-trophy"></i></div>
                                        <div>
                                            <div class="fw-semibold"><?= e($row['title']) ?></div>
                                            <div class="small text-muted mb-1"><?= e(resume_month_year($row['achieved_on'])) ?></div>
                                            <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= e($row['description']) ?></p><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'languages': ?>
                <section id="languages" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Communication</div>
                        <h2 class="portfolio-section__title mb-4">Languages</h2>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($languages as $row): ?>
                                <span class="portfolio-tech-badge"><?= e($row['language_name']) ?> &middot; <?= e(ucfirst($row['proficiency'])) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'research': ?>
                <section id="research" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Publications</div>
                        <h2 class="portfolio-section__title mb-4">Research &amp; Publications</h2>
                        <div class="row g-3">
                            <?php foreach ($researchItems as $row): ?>
                                <div class="col-md-6">
                                    <div class="portfolio-cert-card">
                                        <span class="portfolio-tech-badge mb-2 d-inline-block"><?= e($typeLabels[$row['type']] ?? $row['type']) ?></span>
                                        <div class="fw-semibold"><?= e($row['title']) ?></div>
                                        <?php if (!empty($row['authors_collaborators'])): ?><div class="small text-muted mb-1"><?= e($row['authors_collaborators']) ?></div><?php endif; ?>
                                        <?php if (!empty($isPublicView)): ?><a href="<?= url('/research-hub/' . $row['id']) ?>" class="small fw-semibold">View details</a><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'hire_me': ?>
                <section id="hire_me" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-hire-me">
                            <div class="portfolio-section__eyebrow" style="color: var(--r2j-gold);">Open to work</div>
                            <h2 class="mb-3">Hire Me</h2>
                            <div class="row g-4">
                                <?php if (!empty($interestedRoles)): ?>
                                    <div class="col-md-6">
                                        <div class="small text-uppercase fw-semibold mb-2" style="opacity:.7;">Interested roles</div>
                                        <div class="d-flex flex-wrap gap-2"><?php foreach ($interestedRoles as $role): ?><span class="portfolio-pref-chip"><?= e($role) ?></span><?php endforeach; ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($preferredLocations)): ?>
                                    <div class="col-md-6">
                                        <div class="small text-uppercase fw-semibold mb-2" style="opacity:.7;">Preferred locations</div>
                                        <div class="d-flex flex-wrap gap-2"><?php foreach ($preferredLocations as $loc): ?><span class="portfolio-pref-chip"><?= e($loc) ?></span><?php endforeach; ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($workTypes)): ?>
                                    <div class="col-md-6">
                                        <div class="small text-uppercase fw-semibold mb-2" style="opacity:.7;">Work type</div>
                                        <div class="d-flex flex-wrap gap-2"><?php foreach ($workTypes as $type): ?><span class="portfolio-pref-chip"><?= e($type) ?></span><?php endforeach; ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($profile['availability'])): ?>
                                    <div class="col-md-6">
                                        <div class="small text-uppercase fw-semibold mb-2" style="opacity:.7;">Availability</div>
                                        <span class="portfolio-pref-chip"><?= e($availabilityLabels[$profile['availability']] ?? '') ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="#contact" class="btn btn-lg mt-4 fw-semibold" style="background: var(--r2j-gradient); color: var(--r2j-primary-fill-text);">Get in touch</a>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'contact': ?>
                <section id="contact" class="portfolio-section">
                    <div class="container">
                        <div class="portfolio-section__eyebrow">Let's connect</div>
                        <h2 class="portfolio-section__title mb-4">Contact</h2>
                        <div class="row g-3">
                            <?php if (!empty($account['email'])): ?>
                                <div class="col-md-6">
                                    <a href="mailto:<?= e($account['email']) ?>" class="portfolio-contact-item">
                                        <span class="portfolio-contact-item__icon"><i class="bi bi-envelope"></i></span>
                                        <span><?= e($account['email']) ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($account['phone'])): ?>
                                <div class="col-md-6">
                                    <a href="tel:<?= e($account['phone']) ?>" class="portfolio-contact-item">
                                        <span class="portfolio-contact-item__icon"><i class="bi bi-telephone"></i></span>
                                        <span><?= e($account['phone']) ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($socialRow as $field => $socialMeta): ?>
                                <div class="col-md-6">
                                    <a href="<?= e($profile[$field]) ?>" target="_blank" rel="noopener" class="portfolio-contact-item">
                                        <span class="portfolio-contact-item__icon"><i class="bi <?= $socialMeta['icon'] ?>"></i></span>
                                        <span><?= e($socialMeta['label']) ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4 no-print">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="portfolioCopyLink"><i class="bi bi-link-45deg me-1"></i>Copy link</button>
                            <a href="<?= e($shareLinks['whatsapp']) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm"><i class="bi bi-whatsapp me-1"></i>Share</a>
                            <a href="<?= e($shareLinks['linkedin']) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm"><i class="bi bi-linkedin me-1"></i>Share</a>
                        </div>
                    </div>
                </section>
            <?php break;

        endswitch; ?>
    <?php endforeach; ?>

    <footer class="portfolio-footer no-print">
        <div class="container">
            <div class="fw-bold fs-5 mb-1"><?= e($fullName) ?></div>
            <?php if (!empty($profile['headline'])): ?><div class="small mb-3" style="opacity:.8;"><?= e($profile['headline']) ?></div><?php endif; ?>
            <?php if (!empty($socialRow)): ?>
                <div class="d-flex flex-wrap gap-2 justify-content-center portfolio-social-row mb-3">
                    <?php foreach ($socialRow as $field => $socialMeta): ?>
                        <a href="<?= e($profile[$field]) ?>" target="_blank" rel="noopener" aria-label="<?= e($socialMeta['label']) ?>"><i class="bi <?= $socialMeta['icon'] ?>"></i></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="small" style="opacity:.6;">Built with <a href="<?= url('/') ?>">Road2Job</a> &middot; &copy; <?= date('Y') ?></div>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('portfolioCopyLink');
    if (!btn) return;
    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(<?= json_encode($portfolioUrl) ?>).then(function () {
            var original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied';
            setTimeout(function () { btn.innerHTML = original; }, 1500);
        });
    });
});
</script>
