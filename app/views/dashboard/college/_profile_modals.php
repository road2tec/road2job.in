<!-- Department stat modal -->
<div class="modal fade" id="deptStatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Department Placement Stat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-department-name">Department</label><input id="profile-modals-department-name" type="text" name="department_name" class="form-control" placeholder="e.g. Computer Science" required></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-academic-year">Academic year</label><input id="profile-modals-academic-year" type="text" name="academic_year" class="form-control" placeholder="e.g. 2023-24"></div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-total-students">Total students</label><input id="profile-modals-total-students" type="number" name="total_students" class="form-control" min="0"></div>
                        <div class="col"><label class="form-label" for="profile-modals-students-placed">Students placed</label><input id="profile-modals-students-placed" type="number" name="students_placed" class="form-control" min="0"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-average-package">Average package (&#8377;/yr)</label><input id="profile-modals-average-package" type="number" name="average_package" class="form-control" min="0"></div>
                        <div class="col"><label class="form-label" for="profile-modals-highest-package">Highest package (&#8377;/yr)</label><input id="profile-modals-highest-package" type="number" name="highest_package" class="form-control" min="0"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alumni modal -->
<div class="modal fade" id="alumniModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Alumnus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label" for="profile-modals-name">Name</label><input id="profile-modals-name" type="text" name="name" class="form-control" required></div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-batch-year">Batch year</label><input id="profile-modals-batch-year" type="number" name="batch_year" class="form-control" min="1950" max="<?= date('Y') ?>"></div>
                        <div class="col"><label class="form-label" for="profile-modals-department">Department</label><input id="profile-modals-department" type="text" name="department" class="form-control"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label" for="profile-modals-current-position">Current position</label><input id="profile-modals-current-position" type="text" name="current_position" class="form-control"></div>
                        <div class="col"><label class="form-label" for="profile-modals-current-company">Current company</label><input id="profile-modals-current-company" type="text" name="current_company" class="form-control"></div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-testimonial">Testimonial (optional)</label><textarea id="profile-modals-testimonial" name="testimonial" class="form-control" rows="2"></textarea></div>
                    <div class="mb-2"><label class="form-label" for="profile-modals-photo">Photo</label><input id="profile-modals-photo" type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
