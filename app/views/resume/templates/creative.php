<?php
/**
 * Fresher / Student - repurposed from the old two-column colored-sidebar
 * "Creative" layout (dropped: it read as a graphic-design resume, not a
 * conservative one, and multi-column layouts are harder for ATS parsers).
 * Same conservative single-column structure as the other two templates,
 * but reorders emphasis for early-career candidates: Education and Skills
 * surface directly under the summary, ahead of a possibly-thin or empty
 * Experience section, since that's usually the stronger material a
 * student/fresher actually has.
 */
$hasSummary = !empty($profile['career_objective']);
$summaryHeading = !empty($experience) ? 'Professional Summary' : 'Career Objective';
$portfolioUrl = !empty($account['username']) ? url('/u/' . $account['username']) : null;
?>
<div class="mb-3 pb-2" style="border-bottom: 1px solid #dee2e6;">
    <h1 class="fw-bold mb-1" style="font-size: 1.7rem;"><?= e($account['full_name'] ?? '') ?></h1>
    <?php if (!empty($profile['headline'])): ?>
        <p class="mb-1" style="font-size: .95rem;"><?= e($profile['headline']) ?></p>
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
        <h2 class="resume-heading"><?= $summaryHeading ?></h2>
        <p class="mb-0"><?= nl2br(e($profile['career_objective'])) ?></p>
    </section>
<?php endif; ?>

<?php if (!empty($education)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading">Education</h2>
        <?php foreach ($education as $row): ?>
            <div class="resume-entry mb-2">
                <div class="d-flex justify-content-between flex-wrap">
                    <strong><?= e($row['degree']) ?><?= !empty($row['field_of_study']) ? ' - ' . e($row['field_of_study']) : '' ?></strong>
                    <span class="small"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null)) ?></span>
                </div>
                <div class="small"><?= e($row['institution_name']) ?><?= !empty($row['grade']) ? ' &middot; Grade: ' . e($row['grade']) : '' ?></div>
                <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($skills)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading">Core Skills</h2>
        <p class="mb-0"><?= e(implode(" \u{2022} ", array_map(fn ($row) => $row['skill_name'], $skills))) ?></p>
    </section>
<?php endif; ?>

<?php if (!empty($projects)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading">Projects</h2>
        <?php foreach ($projects as $row): ?>
            <div class="resume-entry mb-2">
                <div class="d-flex justify-content-between flex-wrap">
                    <strong><?= e($row['title']) ?></strong>
                    <span class="small"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null)) ?></span>
                </div>
                <?php if (!empty($row['project_url'])): ?><div class="small"><a href="<?= e($row['project_url']) ?>"><?= e($row['project_url']) ?></a></div><?php endif; ?>
                <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($experience)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading">Experience</h2>
        <?php foreach ($experience as $row): ?>
            <div class="resume-entry mb-2">
                <div class="d-flex justify-content-between flex-wrap">
                    <strong><?= e($row['job_title']) ?><?= $row['employment_type'] === 'internship' ? ' (Internship)' : '' ?></strong>
                    <span class="small"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, (bool) $row['currently_working'])) ?></span>
                </div>
                <div class="small mb-1"><?= e($row['company_name']) ?></div>
                <?php if (!empty($row['description'])): ?><p class="small mb-0"><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($certificates)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading">Certifications</h2>
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
        <h2 class="resume-heading">Achievements</h2>
        <?php foreach ($achievements as $row): ?>
            <div class="resume-entry mb-1 small"><strong><?= e($row['title']) ?></strong><?= !empty($row['description']) ? ' - ' . e($row['description']) : '' ?></div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($research)): ?>
    <section class="resume-section mb-3">
        <h2 class="resume-heading">Research &amp; Publications</h2>
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
        <h2 class="resume-heading">Languages</h2>
        <p class="mb-0"><?= e(implode(', ', array_map(fn ($l) => $l['language_name'] . ' (' . ucfirst($l['proficiency']) . ')', $languages))) ?></p>
    </section>
<?php endif; ?>
