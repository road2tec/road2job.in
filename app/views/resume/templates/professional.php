<?php
/**
 * Modern Professional - same conservative single-column structure as ATS
 * Classic, with a touch more typographic character: a heavier name
 * treatment, a thin accent rule under the header, and a restrained orange
 * accent on section headings (not a fill/block - just the heading text).
 */
$hasSummary = !empty($profile['career_objective']);
$summaryHeading = !empty($experience) ? 'Professional Summary' : 'Career Objective';
$portfolioUrl = !empty($account['username']) ? url('/u/' . $account['username']) : null;
?>
<div class="mb-3 pb-2" style="border-bottom: 2px solid #f5900a;">
    <h1 class="fw-bold mb-1" style="font-size: 1.9rem; letter-spacing: .01em;"><?= e($account['full_name'] ?? '') ?></h1>
    <?php if (!empty($profile['headline'])): ?>
        <p class="mb-1 fw-semibold" style="font-size: 1rem; color: #f5900a;"><?= e($profile['headline']) ?></p>
    <?php endif; ?>
    <p class="mb-0 small">
        <?php
        $contactParts = array_filter([
            $account['phone'] ?? null,
            $account['email'] ?? null,
            !empty($profile['city']) ? $profile['city'] . (!empty($profile['state']) ? ', ' . $profile['state'] : '') : null,
        ]);
        echo e(implode(' | ', $contactParts));
        ?>
    </p>
    <?php
    $linkParts = [];
    if ($portfolioUrl) $linkParts[] = ['label' => 'Portfolio', 'url' => $portfolioUrl];
    if (!empty($profile['linkedin_url'])) $linkParts[] = ['label' => 'LinkedIn', 'url' => $profile['linkedin_url']];
    if (!empty($profile['github_url'])) $linkParts[] = ['label' => 'GitHub', 'url' => $profile['github_url']];
    ?>
    <?php if (!empty($linkParts)): ?>
        <p class="mb-0 small">
            <?php foreach ($linkParts as $i => $link): ?>
                <?= $i > 0 ? ' | ' : '' ?><a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>
</div>

<?php if ($hasSummary): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent"><?= $summaryHeading ?></h2>
        <p class="mb-0"><?= nl2br(e($profile['career_objective'])) ?></p>
    </section>
<?php endif; ?>

<?php if (!empty($skills)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent">Core Skills</h2>
        <p class="mb-0"><?= e(implode(" \u{2022} ", array_map(fn ($row) => $row['skill_name'], $skills))) ?></p>
    </section>
<?php endif; ?>

<?php if (!empty($experience)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent">Professional Experience</h2>
        <?php foreach ($experience as $row): ?>
            <div class="resume-entry mb-2">
                <div class="d-flex justify-content-between flex-wrap">
                    <strong><?= e($row['job_title']) ?><?= $row['employment_type'] === 'internship' ? ' (Internship)' : '' ?></strong>
                    <span class="small text-muted"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, (bool) $row['currently_working'])) ?></span>
                </div>
                <div class="small mb-1 text-muted"><?= e($row['company_name']) ?></div>
                <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($projects)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent">Projects</h2>
        <?php foreach ($projects as $row): ?>
            <div class="resume-entry mb-2">
                <div class="d-flex justify-content-between flex-wrap">
                    <strong><?= e($row['title']) ?></strong>
                    <span class="small text-muted"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null)) ?></span>
                </div>
                <?php if (!empty($row['project_url'])): ?><div class="small"><a href="<?= e($row['project_url']) ?>"><?= e($row['project_url']) ?></a></div><?php endif; ?>
                <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($education)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent">Education</h2>
        <?php foreach ($education as $row): ?>
            <div class="resume-entry mb-2">
                <div class="d-flex justify-content-between flex-wrap">
                    <strong><?= e($row['degree']) ?><?= !empty($row['field_of_study']) ? ' - ' . e($row['field_of_study']) : '' ?></strong>
                    <span class="small text-muted"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null)) ?></span>
                </div>
                <div class="small text-muted"><?= e($row['institution_name']) ?><?= !empty($row['grade']) ? ' &middot; Grade: ' . e($row['grade']) : '' ?></div>
                <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($certificates)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent">Certifications</h2>
        <?php foreach ($certificates as $row): ?>
            <div class="resume-entry mb-1 small">
                <strong><?= e($row['title']) ?></strong> &mdash; <?= e($row['issuing_organization']) ?><?php $issueDate = resume_month_year($row['issue_date']); ?><?= $issueDate !== '' ? ' | ' . e($issueDate) : '' ?>
                <?php if (!empty($row['credential_url'])): ?> &middot; <a href="<?= e($row['credential_url']) ?>">View credential</a><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($achievements)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent">Achievements</h2>
        <?php foreach ($achievements as $row): ?>
            <div class="resume-entry mb-1 small"><strong><?= e($row['title']) ?></strong><?= !empty($row['description']) ? ' - ' . e($row['description']) : '' ?></div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($research)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading resume-heading-accent">Research &amp; Publications</h2>
        <?php foreach ($research as $row): ?>
            <div class="resume-entry mb-1 small">
                <strong><?= e($row['title']) ?></strong><?php $pubDate = resume_month_year($row['publication_date'] ?? null); ?><?= $pubDate !== '' ? ' (' . e($pubDate) . ')' : '' ?>
                <?php if (!empty($row['authors_collaborators'])): ?><br><?= e($row['authors_collaborators']) ?><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($languages)): ?>
    <section class="resume-section mb-0">
        <h2 class="resume-heading resume-heading-accent">Languages</h2>
        <p class="mb-0"><?= e(implode(', ', array_map(fn ($l) => $l['language_name'] . ' (' . ucfirst($l['proficiency']) . ')', $languages))) ?></p>
    </section>
<?php endif; ?>
