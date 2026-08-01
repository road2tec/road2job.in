<?php
/**
 * ATS Classic - the default template. Styled to match a specific reference
 * resume the user provided (corporate/Word-template look: blue name with a
 * full-width underline, blue Title-Case section headings, lighter-blue
 * company/institution sub-lines, bulleted skills and bulleted description
 * points) rather than the more minimal grayscale look used elsewhere.
 * Still single column, still actual selectable text - the color scheme is
 * the only thing that sets it apart from a plain ATS layout.
 */
$hasSummary = !empty($profile['career_objective']);
$summaryHeading = !empty($experience) ? 'Professional Summary' : 'Career Objective';
$portfolioUrl = !empty($account['username']) ? url('/u/' . $account['username']) : null;
?>
<div class="ats-header mb-3">
    <h1 class="ats-name mb-0"><?= e($account['full_name'] ?? '') ?></h1>
    <?php if (!empty($profile['headline'])): ?>
        <p class="fw-bold mb-1 mt-2" style="font-size: .95rem;"><?= e($profile['headline']) ?></p>
    <?php endif; ?>
    <?php
    $line1 = array_filter([
        !empty($account['phone']) ? 'Phone: ' . $account['phone'] : null,
        !empty($profile['city']) ? $profile['city'] . (!empty($profile['state']) ? ', ' . $profile['state'] : '') : null,
    ]);
    ?>
    <?php if (!empty($line1)): ?><p class="mb-0 small"><?= e(implode(' | ', $line1)) ?></p><?php endif; ?>
    <?php
    $line2 = [];
    if (!empty($account['email'])) $line2[] = 'Email: ' . $account['email'];
    if (!empty($profile['linkedin_url'])) $line2[] = 'LinkedIn: ' . preg_replace('#^https?://(www\.)?#', '', $profile['linkedin_url']);
    if (!empty($profile['github_url'])) $line2[] = 'GitHub: ' . preg_replace('#^https?://(www\.)?#', '', $profile['github_url']);
    if ($portfolioUrl) $line2[] = 'Portfolio: ' . preg_replace('#^https?://#', '', $portfolioUrl);
    ?>
    <?php if (!empty($line2)): ?><p class="mb-0 small"><?= e(implode(' | ', $line2)) ?></p><?php endif; ?>
</div>

<?php if ($hasSummary): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading"><?= $summaryHeading ?></h2>
        <p class="mb-0"><?= nl2br(e($profile['career_objective'])) ?></p>
    </section>
<?php endif; ?>

<?php if (!empty($skills)): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading">Core Skills</h2>
        <ul class="ats-bullets mb-0">
            <?php foreach ($skills as $row): ?>
                <li><?= e($row['skill_name']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if (!empty($experience)): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading">Professional Experience</h2>
        <?php foreach ($experience as $row): ?>
            <div class="resume-entry mb-2">
                <div class="fw-bold"><?= e($row['job_title']) ?><?= $row['employment_type'] === 'internship' ? ' (Internship)' : '' ?></div>
                <div class="d-flex justify-content-between flex-wrap">
                    <span class="ats-subheading"><?= e($row['company_name']) ?></span>
                    <span class="small text-muted"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, (bool) $row['currently_working'])) ?></span>
                </div>
                <?php $bullets = resume_bullet_lines($row['description'] ?? null); ?>
                <?php if (!empty($bullets)): ?>
                    <ul class="ats-bullets mb-0">
                        <?php foreach ($bullets as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($projects)): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading">Projects</h2>
        <?php foreach ($projects as $row): ?>
            <div class="resume-entry mb-2">
                <div class="d-flex justify-content-between flex-wrap">
                    <span class="fw-bold"><?= e($row['title']) ?></span>
                    <span class="small text-muted"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null)) ?></span>
                </div>
                <?php if (!empty($row['project_url'])): ?><div class="small"><a href="<?= e($row['project_url']) ?>"><?= e($row['project_url']) ?></a></div><?php endif; ?>
                <?php $bullets = resume_bullet_lines($row['description'] ?? null); ?>
                <?php if (!empty($bullets)): ?>
                    <ul class="ats-bullets mb-0">
                        <?php foreach ($bullets as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($education)): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading">Education</h2>
        <?php foreach ($education as $row): ?>
            <div class="resume-entry mb-1">
                <div class="d-flex justify-content-between flex-wrap">
                    <span>
                        <?= e($row['degree']) ?><?= !empty($row['field_of_study']) ? ' - ' . e($row['field_of_study']) : '' ?>
                        &ndash; <?= e($row['institution_name']) ?><?= !empty($row['grade']) ? ' (' . e($row['grade']) . ')' : '' ?>
                    </span>
                    <span class="small text-muted"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null)) ?></span>
                </div>
                <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($certificates)): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading">Certifications</h2>
        <ul class="ats-bullets mb-0">
            <?php foreach ($certificates as $row): ?>
                <li>
                    <?= e($row['title']) ?> &mdash; <?= e($row['issuing_organization']) ?><?php $issueDate = resume_month_year($row['issue_date']); ?><?= $issueDate !== '' ? ' (' . e($issueDate) . ')' : '' ?>
                    <?php if (!empty($row['credential_url'])): ?> &middot; <a href="<?= e($row['credential_url']) ?>">View credential</a><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if (!empty($achievements)): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading">Achievements</h2>
        <ul class="ats-bullets mb-0">
            <?php foreach ($achievements as $row): ?>
                <li><strong><?= e($row['title']) ?></strong><?= !empty($row['description']) ? ' - ' . e($row['description']) : '' ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if (!empty($research)): ?>
    <section class="resume-section mb-3">
        <h2 class="ats-heading">Research &amp; Publications</h2>
        <ul class="ats-bullets mb-0">
            <?php foreach ($research as $row): ?>
                <li>
                    <strong><?= e($row['title']) ?></strong><?php $pubDate = resume_month_year($row['publication_date'] ?? null); ?><?= $pubDate !== '' ? ' (' . e($pubDate) . ')' : '' ?>
                    <?php if (!empty($row['authors_collaborators'])): ?><br><?= e($row['authors_collaborators']) ?><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if (!empty($languages)): ?>
    <section class="resume-section mb-0">
        <h2 class="ats-heading">Languages</h2>
        <p class="mb-0"><?= e(implode(', ', array_map(fn ($l) => $l['language_name'] . ' (' . ucfirst($l['proficiency']) . ')', $languages))) ?></p>
    </section>
<?php endif; ?>
