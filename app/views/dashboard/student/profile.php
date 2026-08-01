<h1 class="h4 mb-1">My Profile</h1>
<p class="text-muted mb-4">Everything here feeds your resume preview and, later, your public portfolio.</p>

<div class="card mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <span class="fw-semibold">Profile strength</span>
            <span class="fw-bold"><?= (int) $profileScore['percent'] ?>%</span>
        </div>
        <div class="progress mb-2" style="height: 6px;">
            <div class="progress-bar" role="progressbar" style="width: <?= (int) $profileScore['percent'] ?>%;" aria-valuenow="<?= (int) $profileScore['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="small text-muted">
            <?php foreach ($profileScore['items'] as $item): ?>
                <span class="me-3"><i class="bi <?= $item['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i> <?= e($item['label']) ?></span>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($profileScore['nextAction'])): ?>
            <div class="small text-muted mt-2"><i class="bi bi-lightbulb me-1"></i><?= e($profileScore['nextAction']) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-person-badge me-2 text-primary"></i>Personal information</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/profile') ?>" enctype="multipart/form-data" data-guard-submit>
            <?= csrf_field() ?>

            <div class="row g-3 mb-3 align-items-center">
                <div class="col-auto">
                    <?php if (!empty($profile['avatar_path'])): ?>
                        <img src="<?= url($profile['avatar_path']) ?>" alt="Avatar" class="rounded-circle border" loading="lazy" style="width:72px;height:72px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center feature-icon" style="width:72px;height:72px;">
                            <i class="bi bi-person fs-3"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <label class="form-label" for="profile-avatar">Profile photo</label>
                    <input id="profile-avatar" type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="profile-full-name">Full name</label>
                    <input id="profile-full-name" type="text" name="full_name" class="form-control" value="<?= e($account['full_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-headline">Headline</label>
                    <input id="profile-headline" type="text" name="headline" class="form-control" placeholder="e.g. Final-year CS student" value="<?= e($profile['headline'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-date-of-birth">Date of birth</label>
                    <input id="profile-date-of-birth" type="date" name="date_of_birth" class="form-control" value="<?= e($profile['date_of_birth'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-gender">Gender</label>
                    <select id="profile-gender" name="gender" class="form-select">
                        <option value="">Prefer not to say</option>
                        <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($profile['gender'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-city">City</label>
                    <input id="profile-city" type="text" name="city" class="form-control" value="<?= e($profile['city'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-state">State</label>
                    <input id="profile-state" type="text" name="state" class="form-control" value="<?= e($profile['state'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-country">Country</label>
                    <input id="profile-country" type="text" name="country" class="form-control" value="<?= e($profile['country'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-pincode">Pincode</label>
                    <input id="profile-pincode" type="text" name="pincode" class="form-control" value="<?= e($profile['pincode'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="profile-address-line">Address</label>
                    <input id="profile-address-line" type="text" name="address_line" class="form-control" value="<?= e($profile['address_line'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="profile-career-objective">Career objective</label>
                    <textarea id="profile-career-objective" name="career_objective" class="form-control" rows="2"><?= e($profile['career_objective'] ?? '') ?></textarea>
                </div>
                <div class="col-12"><hr class="my-2"></div>
                <div class="col-12">
                    <h6 class="fw-semibold mb-0">Career preferences</h6>
                    <p class="small text-muted mb-0">Powers the "Hire me" section on your public portfolio - leave blank to hide it there.</p>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Interested roles</label>
                    <div id="interestedRolesRoot" class="ms-root" data-multi-select="chips" data-ms-name="interested_roles" data-ms-catalog="#prefsJobTitlesCatalog" data-ms-placeholder="Type a role and press Enter..." data-ms-max="6">
                        <div data-ms-chips class="ms-chips"></div>
                        <input type="text" data-ms-input class="form-control">
                        <div data-ms-dropdown class="ms-dropdown"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Preferred locations</label>
                    <div id="preferredLocationsRoot" class="ms-root" data-multi-select="chips" data-ms-name="preferred_locations" data-ms-catalog="#prefsLocationsCatalog" data-ms-placeholder="Type a city or Remote..." data-ms-max="6">
                        <div data-ms-chips class="ms-chips"></div>
                        <input type="text" data-ms-input class="form-control">
                        <div data-ms-dropdown class="ms-dropdown"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Domains of interest</label>
                    <div id="domainsOfInterestRoot" class="ms-root" data-multi-select="chips" data-ms-name="domains_of_interest" data-ms-catalog="#prefsDomainsCatalog" data-ms-placeholder="e.g. Web Development..." data-ms-max="6">
                        <div data-ms-chips class="ms-chips"></div>
                        <input type="text" data-ms-input class="form-control">
                        <div data-ms-dropdown class="ms-dropdown"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-availability">Availability</label>
                    <select id="profile-availability" name="availability" class="form-select">
                        <option value="">Not specified</option>
                        <?php $availabilityOptions = ['actively_looking' => 'Actively looking', 'open_to_opportunities' => 'Open to opportunities', 'not_looking' => 'Not looking currently']; ?>
                        <?php foreach ($availabilityOptions as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($profile['availability'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Work type</label>
                    <?php $workTypeSelected = array_map('trim', explode(',', $profile['work_type'] ?? '')); ?>
                    <?php foreach (['On-site', 'Remote', 'Hybrid'] as $workOpt): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="work_type[]" value="<?= e($workOpt) ?>" id="worktype-<?= strtolower($workOpt) ?>" <?= in_array($workOpt, $workTypeSelected, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="worktype-<?= strtolower($workOpt) ?>"><?= e($workOpt) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-12"><hr class="my-2"></div>
                <div class="col-12">
                    <h6 class="fw-semibold mb-0">Social &amp; coding profiles</h6>
                    <p class="small text-muted mb-0">These power your public portfolio's contact/social row - fill in only what you have.</p>
                </div>
                <div class="col-12">
                    <label class="form-label" for="social-quick-add">Quick add - paste any profile link</label>
                    <input type="url" id="social-quick-add" class="form-control" placeholder="Paste a LinkedIn, GitHub, LeetCode... link and press Enter">
                    <div id="social-quick-add-status" class="small text-success mt-1"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-linkedin-url">LinkedIn URL</label>
                    <input id="profile-linkedin-url" type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/yourname" value="<?= e($profile['linkedin_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-github-url">GitHub URL</label>
                    <input id="profile-github-url" type="url" name="github_url" class="form-control" placeholder="https://github.com/yourname" value="<?= e($profile['github_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-leetcode-url">LeetCode URL</label>
                    <input id="profile-leetcode-url" type="url" name="leetcode_url" class="form-control" placeholder="https://leetcode.com/yourname" value="<?= e($profile['leetcode_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-hackerrank-url">HackerRank URL</label>
                    <input id="profile-hackerrank-url" type="url" name="hackerrank_url" class="form-control" placeholder="https://hackerrank.com/yourname" value="<?= e($profile['hackerrank_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-codechef-url">CodeChef URL</label>
                    <input id="profile-codechef-url" type="url" name="codechef_url" class="form-control" placeholder="https://codechef.com/users/yourname" value="<?= e($profile['codechef_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-behance-url">Behance URL</label>
                    <input id="profile-behance-url" type="url" name="behance_url" class="form-control" placeholder="https://behance.net/yourname" value="<?= e($profile['behance_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-dribbble-url">Dribbble URL</label>
                    <input id="profile-dribbble-url" type="url" name="dribbble_url" class="form-control" placeholder="https://dribbble.com/yourname" value="<?= e($profile['dribbble_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-youtube-url">YouTube URL</label>
                    <input id="profile-youtube-url" type="url" name="youtube_url" class="form-control" placeholder="https://youtube.com/@yourname" value="<?= e($profile['youtube_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-website-url">Personal website URL</label>
                    <input id="profile-website-url" type="url" name="website_url" class="form-control" placeholder="https://yourname.dev" value="<?= e($profile['website_url'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save changes</button>
            <a href="<?= url('/dashboard/resume') ?>" class="btn btn-outline-secondary mt-3" target="_blank">Preview resume</a>
        </form>
    </div>
</div>

<script type="application/json" id="prefsJobTitlesCatalog"><?= json_encode($jobTitlesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="prefsLocationsCatalog"><?= json_encode($locationsCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="prefsDomainsCatalog"><?= json_encode($domainsCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>

<?php if (!empty($timeline)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-signpost-2 me-2 text-primary"></i>Career timeline</div>
    <div class="card-body">
        <ul class="timeline mb-0">
            <?php foreach ($timeline as $item): ?>
                <li class="timeline-item">
                    <div class="small text-muted"><?= e($item['type']) ?> &middot; <?= $item['date'] ?></div>
                    <div><?= e($item['title']) ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Education -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-mortarboard me-2 text-primary"></i>Education</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#educationModal" onclick="resetResourceModal('educationModal', '<?= url('/dashboard/profile/education') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($education)): ?>
            <p class="text-muted small mb-0">No education added yet.</p>
        <?php endif; ?>
        <?php foreach ($education as $row): ?>
            <div class="d-flex justify-content-between align-items-start profile-row">
                <div>
                    <strong><?= e($row['degree']) ?></strong><?= !empty($row['field_of_study']) ? ' - ' . e($row['field_of_study']) : '' ?><br>
                    <span class="small text-muted"><?= e($row['institution_name']) ?> &middot; <?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, empty($row['end_date']))) ?></span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#educationModal"
                        onclick='openResourceModal("educationModal", "<?= url('/dashboard/profile/education/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/profile/education/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this entry?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Skills -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-stars me-2 text-primary"></i>Skills</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#skillModal" onclick="resetResourceModal('skillModal')">Add skills</button>
    </div>
    <div class="card-body">
        <?php if (empty($skills)): ?>
            <p class="text-muted small mb-0">No skills added yet - add several at once below.</p>
        <?php endif; ?>
        <?php foreach ($skills as $row): ?>
            <span class="badge text-bg-light border me-2 mb-2 p-2">
                <?= e($row['skill_name']) ?> <span class="text-muted">(<?= e($row['proficiency']) ?>)</span>
                <a href="#" class="text-decoration-none ms-1" data-bs-toggle="modal" data-bs-target="#skillEditModal" aria-label="Edit <?= e($row['skill_name']) ?>"
                    onclick='openResourceModal("skillEditModal", "<?= url('/dashboard/profile/skills/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>); return false;'><i class="bi bi-pencil"></i></a>
                <form method="post" action="<?= url('/dashboard/profile/skills/' . $row['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this skill?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link btn-sm p-0 ms-1 text-danger" aria-label="Remove <?= e($row['skill_name']) ?>"><i class="bi bi-x"></i></button>
                </form>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<!-- Projects -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-kanban me-2 text-primary"></i>Projects</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal" onclick="resetResourceModal('projectModal', '<?= url('/dashboard/profile/projects') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($projects)): ?>
            <p class="text-muted small mb-0">No projects added yet.</p>
        <?php endif; ?>
        <?php foreach ($projects as $row): ?>
            <div class="d-flex justify-content-between align-items-start profile-row">
                <div>
                    <strong><?= e($row['title']) ?></strong><?= !empty($row['role']) ? ' - ' . e($row['role']) : '' ?><?= !empty($row['project_type']) ? ' <span class="badge text-bg-light border">' . e($row['project_type']) . '</span>' : '' ?><br>
                    <span class="small text-muted"><?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, empty($row['end_date']))) ?></span>
                    <?php if (!empty($row['attachment_path'])): ?> &middot; <a href="<?= url($row['attachment_path']) ?>" target="_blank" class="small">Attachment</a><?php endif; ?>
                    <?php if (!empty($row['technologies_used'])): ?><div class="small text-muted mt-1"><i class="bi bi-code-slash me-1"></i><?= e($row['technologies_used']) ?></div><?php endif; ?>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#projectModal"
                        onclick='openResourceModal("projectModal", "<?= url('/dashboard/profile/projects/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/profile/projects/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this project?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Experience -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-briefcase me-2 text-primary"></i>Experience</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#experienceModal" onclick="resetResourceModal('experienceModal', '<?= url('/dashboard/profile/experience') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($experience)): ?>
            <p class="text-muted small mb-0">No experience added yet.</p>
        <?php endif; ?>
        <?php foreach ($experience as $row): ?>
            <div class="d-flex justify-content-between align-items-start profile-row">
                <div>
                    <strong><?= e($row['job_title']) ?></strong> &mdash; <?= e($row['company_name']) ?><?= !empty($row['location']) ? ' <span class="text-muted small">(' . e($row['location']) . ')</span>' : '' ?><br>
                    <span class="small text-muted"><?= e(ucfirst(str_replace('_', ' ', $row['employment_type']))) ?> &middot; <?= e(resume_date_range($row['start_date'], $row['end_date'] ?? null, (bool) $row['currently_working'])) ?></span>
                    <?php if (!empty($row['skills_used'])): ?><div class="small text-muted mt-1"><i class="bi bi-code-slash me-1"></i><?= e($row['skills_used']) ?></div><?php endif; ?>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#experienceModal"
                        onclick='openResourceModal("experienceModal", "<?= url('/dashboard/profile/experience/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/profile/experience/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this entry?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Certificates -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-award me-2 text-primary"></i>Certificates</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#certificateModal" onclick="resetResourceModal('certificateModal', '<?= url('/dashboard/profile/certificates') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($certificates)): ?>
            <p class="text-muted small mb-0">No certificates added yet.</p>
        <?php endif; ?>
        <?php foreach ($certificates as $row): ?>
            <div class="d-flex justify-content-between align-items-start profile-row">
                <div>
                    <strong><?= e($row['title']) ?></strong><br>
                    <span class="small text-muted"><?= e($row['issuing_organization']) ?><?php $issueDate = resume_month_year($row['issue_date']); ?><?= $issueDate !== '' ? ' &middot; ' . e($issueDate) : '' ?></span>
                    <?php if (!empty($row['credential_url'])): ?> &middot; <a href="<?= e($row['credential_url']) ?>" target="_blank" class="small">View credential</a><?php endif; ?>
                    <?php if (!empty($row['attachment_path'])): ?> &middot; <a href="<?= url($row['attachment_path']) ?>" target="_blank" class="small">Attachment</a><?php endif; ?>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#certificateModal"
                        onclick='openResourceModal("certificateModal", "<?= url('/dashboard/profile/certificates/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/profile/certificates/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this certificate?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Achievements -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-trophy me-2 text-primary"></i>Achievements</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#achievementModal" onclick="resetResourceModal('achievementModal', '<?= url('/dashboard/profile/achievements') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($achievements)): ?>
            <p class="text-muted small mb-0">No achievements added yet.</p>
        <?php endif; ?>
        <?php foreach ($achievements as $row): ?>
            <div class="d-flex justify-content-between align-items-start profile-row">
                <div>
                    <strong><?= e($row['title']) ?></strong><br>
                    <span class="small text-muted"><?= e(resume_month_year($row['achieved_on'])) ?></span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#achievementModal"
                        onclick='openResourceModal("achievementModal", "<?= url('/dashboard/profile/achievements/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/profile/achievements/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this achievement?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Languages -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-translate me-2 text-primary"></i>Languages</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#languageModal" onclick="resetResourceModal('languageModal', '<?= url('/dashboard/profile/languages') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($languages)): ?>
            <p class="text-muted small mb-0">No languages added yet.</p>
        <?php endif; ?>
        <?php foreach ($languages as $row): ?>
            <span class="badge text-bg-light border me-2 mb-2 p-2">
                <?= e($row['language_name']) ?> <span class="text-muted">(<?= e($row['proficiency']) ?>)</span>
                <a href="#" class="text-decoration-none ms-1" data-bs-toggle="modal" data-bs-target="#languageModal"
                    onclick='openResourceModal("languageModal", "<?= url('/dashboard/profile/languages/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>); return false;'><i class="bi bi-pencil"></i></a>
                <form method="post" action="<?= url('/dashboard/profile/languages/' . $row['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this language?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link btn-sm p-0 ms-1 text-danger"><i class="bi bi-x"></i></button>
                </form>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<?php Core\View::partial('dashboard/student/_profile_modals', [
    'skillsCatalog' => $skillsCatalog,
    'degreesCatalog' => $degreesCatalog,
    'branchesCatalog' => $branchesCatalog,
    'jobTitlesCatalog' => $jobTitlesCatalog,
    'projectTypesCatalog' => $projectTypesCatalog,
    'organizationsCatalog' => $organizationsCatalog,
    'languagesCatalog' => $languagesCatalog,
]); ?>

<script src="<?= asset('js/multi-select.js') ?>"></script>
<script src="<?= asset('js/social-links.js') ?>"></script>
<script src="<?= asset('js/profile.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var prefill = {
        interestedRolesRoot: <?= json_encode(array_values(array_filter(array_map('trim', explode(',', $profile['interested_roles'] ?? ''))))) ?>,
        preferredLocationsRoot: <?= json_encode(array_values(array_filter(array_map('trim', explode(',', $profile['preferred_locations'] ?? ''))))) ?>,
        domainsOfInterestRoot: <?= json_encode(array_values(array_filter(array_map('trim', explode(',', $profile['domains_of_interest'] ?? ''))))) ?>
    };
    Object.keys(prefill).forEach(function (id) {
        var root = document.getElementById(id);
        if (root && typeof root.msPopulate === 'function') root.msPopulate(prefill[id]);
    });
});
</script>
