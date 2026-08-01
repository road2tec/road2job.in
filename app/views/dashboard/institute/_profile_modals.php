<!-- Faculty modal -->
<div class="modal fade" id="facultyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-name">Name</label><input id="profile-modals-name" type="text" name="name" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-designation">Designation</label><input id="profile-modals-designation" type="text" name="designation" class="form-control"></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-expertise">Expertise</label><input id="profile-modals-expertise" type="text" name="expertise" class="form-control" placeholder="e.g. Data Structures, Web Development"></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-bio">Bio</label><textarea id="profile-modals-bio" name="bio" class="form-control" rows="2"></textarea></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-photo">Photo</label><input id="profile-modals-photo" type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Gallery modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= url('/dashboard/institute/gallery') ?>" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-image">Image</label><input id="profile-modals-image" type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-caption">Caption (optional)</label><input id="profile-modals-caption" type="text" name="caption" class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Certificate modal -->
<div class="modal fade" id="certificateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Accreditation / Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-title">Title</label><input id="profile-modals-title" type="text" name="title" class="form-control" placeholder="e.g. ISO 9001:2015" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-issuing-body">Issuing body</label><input id="profile-modals-issuing-body" type="text" name="issuing_body" class="form-control"></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-issued-year">Issued year</label><input id="profile-modals-issued-year" type="number" name="issued_year" class="form-control" min="1900" max="<?= date('Y') ?>"></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-document">Document (optional)</label><input id="profile-modals-document" type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Placement modal -->
<div class="modal fade" id="placementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Placement Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-student-name">Student name</label><input id="profile-modals-student-name" type="text" name="student_name" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-company-name">Company</label><input id="profile-modals-company-name" type="text" name="company_name" class="form-control" required></div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-job-role">Job role</label><input id="profile-modals-job-role" type="text" name="job_role" class="form-control"></div>
                        <div class="col">
                            <label class="form-label" for="profile-modals-placement-type">Type</label>
                            <select id="profile-modals-placement-type" name="placement_type" class="form-select">
                                <option value="">Select</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Internship">Internship</option>
                                <option value="Contract">Contract</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-package-amount">Package (&#8377;/yr)</label><input id="profile-modals-package-amount" type="number" name="package_amount" class="form-control" min="0"></div>
                        <div class="col"><label class="form-label" for="profile-modals-placement-year">Year</label><input id="profile-modals-placement-year" type="number" name="placement_year" class="form-control" min="1990" max="<?= date('Y') ?>"></div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-placement-date">Placement date</label><input id="profile-modals-placement-date" type="date" name="placement_date" class="form-control"></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-course-name">Course</label><input id="profile-modals-course-name" type="text" name="course_name" class="form-control"></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-placement-description">Description / achievement</label><textarea id="profile-modals-placement-description" name="description" class="form-control" rows="2"></textarea></div>
                    <div class="mb-2">
                        <label class="form-label" for="profile-modals-student-photo">Student photo (optional)</label>
                        <input id="profile-modals-student-photo" type="file" name="student_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text">Leave blank to keep the current photo when editing.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
