<!-- Catalog data for the multi-select/autocomplete component (public/assets/js/multi-select.js).
     One shared block, read by data-ms-catalog="#id" selectors below. -->
<script type="application/json" id="skillsCatalogData"><?= json_encode($skillsCatalog['flat'] ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="skillSuggestionsData"><?= json_encode($skillsCatalog['suggestions'] ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="degreesCatalogData"><?= json_encode($degreesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="branchesCatalogData"><?= json_encode($branchesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="jobTitlesCatalogData"><?= json_encode($jobTitlesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="projectTypesCatalogData"><?= json_encode($projectTypesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="organizationsCatalogData"><?= json_encode($organizationsCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="languagesCatalogData"><?= json_encode($languagesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>

<!-- Education modal -->
<div class="modal fade" id="educationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit data-track-unsaved>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Education</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-degree">Degree</label>
                        <div class="ms-root" data-multi-select="single" data-ms-catalog="#degreesCatalogData" data-ms-placeholder="e.g. BE, BTech, MCA...">
                            <input id="profile-modals-degree" type="text" name="degree" class="form-control" data-ms-input required autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-institution-name">Institution</label><input id="profile-modals-institution-name" type="text" name="institution_name" class="form-control" required></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-field-of-study">Field of study / Branch</label>
                        <div class="ms-root" data-multi-select="single" data-ms-catalog="#branchesCatalogData" data-ms-placeholder="e.g. Computer Engineering...">
                            <input id="profile-modals-field-of-study" type="text" name="field_of_study" class="form-control" data-ms-input autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-start-date">Start date</label><input id="profile-modals-start-date" type="date" name="start_date" class="form-control" required></div>
                        <div class="col"><label class="form-label" for="profile-modals-end-date">End date</label><input id="profile-modals-end-date" type="date" name="end_date" class="form-control"></div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-grade">Grade</label><input id="profile-modals-grade" type="text" name="grade" class="form-control"></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-description">Description</label><textarea id="profile-modals-description" name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Skill modal - bulk multi-add, not one-at-a-time -->
<div class="modal fade" id="skillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= url('/dashboard/profile/skills/bulk') ?>" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add skills</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-default-proficiency">Default proficiency for new skills</label>
                        <select id="profile-modals-default-proficiency" class="form-select">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate" selected>Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                        <div class="form-text">Applied to each skill as you add it - change any individual skill's level below afterward.</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label" for="profile-modals-skill-name">Skills</label>
                        <div class="ms-root" data-multi-select="chips" data-ms-name="skills"
                             data-ms-catalog="#skillsCatalogData" data-ms-suggestions="#skillSuggestionsData"
                             data-ms-secondary-name="proficiencies" data-ms-secondary-options="beginner,intermediate,advanced,expert"
                             data-ms-secondary-default="#profile-modals-default-proficiency"
                             data-ms-placeholder="Type a skill and press Enter (or paste a comma-separated list)...">
                            <div class="ms-chips" data-ms-chips></div>
                            <input id="profile-modals-skill-name" type="text" class="form-control" data-ms-input autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                            <div class="ms-related" data-ms-related></div>
                        </div>
                        <div class="form-text">Add as many as you like, then Save once - no need to repeat this for each skill.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save all skills</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Single-skill edit modal (proficiency change / rename one existing skill) -->
<div class="modal fade" id="skillEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-skill-name-edit">Skill name</label><input id="profile-modals-skill-name-edit" type="text" name="skill_name" class="form-control" required></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-proficiency">Proficiency</label>
                        <select id="profile-modals-proficiency" name="proficiency" class="form-select">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Project modal -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit data-track-unsaved>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-title">Title</label><input id="profile-modals-title" type="text" name="title" class="form-control" required></div>
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <label class="form-label" for="profile-modals-role">Your role</label>
                            <input id="profile-modals-role" type="text" name="role" class="form-control" placeholder="e.g. Team lead, Individual">
                        </div>
                        <div class="col">
                            <label class="form-label" for="profile-modals-project-type">Project type</label>
                            <div class="ms-root" data-multi-select="single" data-ms-catalog="#projectTypesCatalogData" data-ms-placeholder="e.g. Web Development...">
                                <input id="profile-modals-project-type" type="text" name="project_type" class="form-control" data-ms-input autocomplete="off">
                                <div class="ms-dropdown" data-ms-dropdown></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-description-2">Description</label><textarea id="profile-modals-description-2" name="description" class="form-control" rows="2" required></textarea></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-technologies">Technologies used</label>
                        <div class="ms-root" data-multi-select="chips" data-ms-name="technologies_used"
                             data-ms-catalog="#skillsCatalogData" data-ms-placeholder="Type a technology and press Enter...">
                            <div class="ms-chips" data-ms-chips></div>
                            <input id="profile-modals-technologies" type="text" class="form-control" data-ms-input autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-project-url">Project / GitHub URL</label><input id="profile-modals-project-url" type="url" name="project_url" class="form-control" placeholder="https://"></div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-start-date-2">Start date</label><input id="profile-modals-start-date-2" type="date" name="start_date" class="form-control" required></div>
                        <div class="col"><label class="form-label" for="profile-modals-end-date-2">End date</label><input id="profile-modals-end-date-2" type="date" name="end_date" class="form-control"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-attachment">Attachment (optional)</label>
                        <input id="profile-modals-attachment" type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">Leave blank to keep the current attachment when editing.</div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="projectIsFeatured">
                        <label class="form-check-label" for="projectIsFeatured">Featured project - show first on my portfolio</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Experience modal -->
<div class="modal fade" id="experienceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit data-track-unsaved>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Experience</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-job-title">Job title</label>
                        <div class="ms-root" data-multi-select="single" data-ms-catalog="#jobTitlesCatalogData" data-ms-placeholder="e.g. Software Developer...">
                            <input id="profile-modals-job-title" type="text" name="job_title" class="form-control" data-ms-input required autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-company-name">Company</label><input id="profile-modals-company-name" type="text" name="company_name" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-location">Location</label><input id="profile-modals-location" type="text" name="location" class="form-control" placeholder="City, Country"></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-employment-type">Employment type</label>
                        <select id="profile-modals-employment-type" name="employment_type" class="form-select">
                            <option value="internship">Internship</option>
                            <option value="full_time">Full-time</option>
                            <option value="part_time">Part-time</option>
                            <option value="freelance">Freelance</option>
                            <option value="contract">Contract</option>
                            <option value="training">Training</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-start-date-3">Start date</label><input id="profile-modals-start-date-3" type="date" name="start_date" class="form-control" required></div>
                        <div class="col"><label class="form-label" for="profile-modals-end-date-3">End date</label><input id="profile-modals-end-date-3" type="date" name="end_date" class="form-control"></div>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="currently_working" value="1" class="form-check-input" id="currentlyWorking">
                        <label class="form-check-label" for="currentlyWorking">I currently work here</label>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-description-3">Description</label><textarea id="profile-modals-description-3" name="description" class="form-control" rows="2"></textarea></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-skills-used">Skills used</label>
                        <div class="ms-root" data-multi-select="chips" data-ms-name="skills_used"
                             data-ms-catalog="#skillsCatalogData" data-ms-placeholder="Type a skill and press Enter...">
                            <div class="ms-chips" data-ms-chips></div>
                            <input id="profile-modals-skills-used" type="text" class="form-control" data-ms-input autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Certificate modal -->
<div class="modal fade" id="certificateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit data-track-unsaved>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-title-2">Title</label><input id="profile-modals-title-2" type="text" name="title" class="form-control" required></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-issuing-organization">Issuing organization</label>
                        <div class="ms-root" data-multi-select="single" data-ms-catalog="#organizationsCatalogData" data-ms-placeholder="e.g. Google, Coursera...">
                            <input id="profile-modals-issuing-organization" type="text" name="issuing_organization" class="form-control" data-ms-input required autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-issue-date">Issue date</label><input id="profile-modals-issue-date" type="date" name="issue_date" class="form-control" required></div>
                        <div class="col"><label class="form-label" for="profile-modals-expiry-date">Expiry date</label><input id="profile-modals-expiry-date" type="date" name="expiry_date" class="form-control"></div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-credential-url">Credential URL</label><input id="profile-modals-credential-url" type="url" name="credential_url" class="form-control" placeholder="https://"></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-attachment-2">Attachment (optional)</label>
                        <input id="profile-modals-attachment-2" type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">Leave blank to keep the current attachment when editing.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Achievement modal -->
<div class="modal fade" id="achievementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit data-track-unsaved>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-title-3">Title</label><input id="profile-modals-title-3" type="text" name="title" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-achieved-on">Date</label><input id="profile-modals-achieved-on" type="date" name="achieved_on" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-description-4">Description</label><textarea id="profile-modals-description-4" name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Language modal -->
<div class="modal fade" id="languageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit data-track-unsaved>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Language</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-language-name">Language</label>
                        <div class="ms-root" data-multi-select="single" data-ms-catalog="#languagesCatalogData" data-ms-placeholder="e.g. English, Hindi...">
                            <input id="profile-modals-language-name" type="text" name="language_name" class="form-control" data-ms-input required autocomplete="off">
                            <div class="ms-dropdown" data-ms-dropdown></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-proficiency-2">Proficiency</label>
                        <select id="profile-modals-proficiency-2" name="proficiency" class="form-select">
                            <option value="basic">Basic</option>
                            <option value="conversational">Conversational</option>
                            <option value="fluent">Fluent</option>
                            <option value="native">Native</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
