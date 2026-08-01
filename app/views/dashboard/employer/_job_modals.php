<!-- Job posting modal -->
<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Job Posting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="job-modals-title">Job title</label><input id="job-modals-title" type="text" name="title" class="form-control" required></div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label" for="job-modals-type">Type</label>
                            <select id="job-modals-type" name="type" class="form-select">
                                <option value="full_time">Full-time</option>
                                <option value="part_time">Part-time</option>
                                <option value="internship">Internship</option>
                                <option value="contract">Contract</option>
                                <option value="remote">Remote</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="job-modals-experience-level">Experience level</label>
                            <select id="job-modals-experience-level" name="experience_level" class="form-select">
                                <option value="fresher">Fresher</option>
                                <option value="junior">Junior</option>
                                <option value="mid">Mid</option>
                                <option value="senior">Senior</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="job-modals-status">Status</label>
                            <select id="job-modals-status" name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-8">
                            <label class="form-label" for="job-modals-location">Location</label>
                            <input id="job-modals-location" type="text" name="location" class="form-control" placeholder="City, Country">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_remote" value="1" class="form-check-input" id="jobIsRemote">
                                <label class="form-check-label" for="jobIsRemote">Remote friendly</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4"><label class="form-label" for="job-modals-min-salary">Min salary (₹/yr)</label><input id="job-modals-min-salary" type="number" name="min_salary" class="form-control" min="0"></div>
                        <div class="col-md-4"><label class="form-label" for="job-modals-max-salary">Max salary (₹/yr)</label><input id="job-modals-max-salary" type="number" name="max_salary" class="form-control" min="0"></div>
                        <div class="col-md-4"><label class="form-label" for="job-modals-application-deadline">Application deadline</label><input id="job-modals-application-deadline" type="date" name="application_deadline" class="form-control"></div>
                    </div>

                    <div class="mb-2"><label class="form-label" for="job-modals-description">Description</label><textarea id="job-modals-description" name="description" class="form-control" rows="3"></textarea></div>
                    <div class="mb-2"><label class="form-label" for="job-modals-requirements">Requirements</label><textarea id="job-modals-requirements" name="requirements" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
