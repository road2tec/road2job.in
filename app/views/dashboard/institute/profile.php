<h1 class="h4 mb-1">Institute Profile</h1>
<p class="text-muted mb-4">This information appears on your public institute page.</p>

<?php if ($profileScore !== null): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-speedometer2 me-2 text-primary"></i>Profile Strength</span>
            <span class="badge text-bg-<?= $profileScore['percent'] >= 80 ? 'success' : ($profileScore['percent'] >= 50 ? 'warning' : 'secondary') ?>"><?= (int) $profileScore['percent'] ?>%</span>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height:.5rem;">
                <div class="progress-bar" role="progressbar" style="width:<?= (int) $profileScore['percent'] ?>%" aria-valuenow="<?= (int) $profileScore['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="row g-2">
                <?php foreach ($profileScore['items'] as $item): ?>
                    <div class="col-6 col-md-4 col-lg-3 small">
                        <i class="bi <?= $item['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?> me-1"></i><?= e($item['label']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($profileScore['nextAction'])): ?>
                <p class="small text-muted mt-3 mb-0"><i class="bi bi-lightbulb me-1"></i><?= e($profileScore['nextAction']) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script type="application/json" id="instituteTypesCatalogData"><?= json_encode($instituteTypesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="specializationsCatalogData"><?= json_encode($specializationsCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/json" id="facilitiesCatalogData"><?= json_encode($facilitiesCatalog ?? [], JSON_UNESCAPED_SLASHES) ?></script>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-easel me-2 text-primary"></i>Institute details</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/institute') ?>" enctype="multipart/form-data" data-guard-submit>
            <?= csrf_field() ?>

            <div class="row g-3 mb-3 align-items-center">
                <div class="col-auto">
                    <?php if (!empty($institute['logo_path'])): ?>
                        <img src="<?= url($institute['logo_path']) ?>" alt="Logo" class="rounded border" loading="lazy" style="width:64px;height:64px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded d-flex align-items-center justify-content-center feature-icon" style="width:64px;height:64px;">
                            <i class="bi bi-easel"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <label class="form-label" for="profile-logo">Institute logo</label>
                    <input id="profile-logo" type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="row g-3 mb-3 align-items-center">
                <div class="col-auto">
                    <?php if (!empty($institute['cover_path'])): ?>
                        <img src="<?= url($institute['cover_path']) ?>" alt="Cover" class="rounded border" loading="lazy" style="width:120px;height:64px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded d-flex align-items-center justify-content-center feature-icon" style="width:120px;height:64px;">
                            <i class="bi bi-image"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <label class="form-label" for="profile-cover">Cover / banner image</label>
                    <input id="profile-cover" type="file" name="cover" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">Shown as the hero banner on your public portfolio.</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="profile-name">Institute name</label>
                    <input id="profile-name" type="text" name="name" class="form-control" value="<?= e($institute['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-institute-type">Institute type</label>
                    <div class="ms-root" data-multi-select="single" data-ms-catalog="#instituteTypesCatalogData" data-ms-placeholder="e.g. Training Institute, Bootcamp...">
                        <input id="profile-institute-type" type="text" name="institute_type" class="form-control" data-ms-input autocomplete="off" value="<?= e($institute['institute_type'] ?? '') ?>">
                        <div class="ms-dropdown" data-ms-dropdown></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-established-year">Established year</label>
                    <input id="profile-established-year" type="number" name="established_year" class="form-control" min="1800" max="<?= date('Y') ?>" value="<?= e($institute['established_year'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-website">Website</label>
                    <input id="profile-website" type="url" name="website" class="form-control" placeholder="https://" value="<?= e($institute['website'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="profile-location">Location</label>
                    <input id="profile-location" type="text" name="location" class="form-control" placeholder="Area, Country" value="<?= e($institute['location'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="profile-city">City</label>
                    <input id="profile-city" type="text" name="city" class="form-control" value="<?= e($institute['city'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="profile-state">State</label>
                    <input id="profile-state" type="text" name="state" class="form-control" value="<?= e($institute['state'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-phone">Contact number</label>
                    <input id="profile-phone" type="text" name="phone" class="form-control" value="<?= e($institute['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-official-email">Official email</label>
                    <input id="profile-official-email" type="email" name="official_email" class="form-control" value="<?= e($institute['official_email'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="profile-description">About the institute</label>
                    <textarea id="profile-description" name="description" class="form-control" rows="4"><?= e($institute['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12"><hr class="my-1"></div>
                <div class="col-12"><h6 class="fw-semibold mb-0">Specializations &amp; facilities</h6></div>
                <div class="col-md-6">
                    <label class="form-label">Specializations</label>
                    <div id="specializationsRoot" class="ms-root" data-multi-select="chips" data-ms-name="specializations" data-ms-catalog="#specializationsCatalogData" data-ms-placeholder="Type and press Enter...">
                        <div class="ms-chips" data-ms-chips></div>
                        <input type="text" class="form-control" data-ms-input autocomplete="off">
                        <div class="ms-dropdown" data-ms-dropdown></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Facilities</label>
                    <div id="facilitiesRoot" class="ms-root" data-multi-select="chips" data-ms-name="facilities" data-ms-catalog="#facilitiesCatalogData" data-ms-placeholder="Type and press Enter...">
                        <div class="ms-chips" data-ms-chips></div>
                        <input type="text" class="form-control" data-ms-input autocomplete="off">
                        <div class="ms-dropdown" data-ms-dropdown></div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Training modes</label>
                    <?php $trainingModesSelected = array_map('trim', explode(',', $institute['training_modes'] ?? '')); ?>
                    <?php foreach (['Online', 'Offline', 'Hybrid'] as $mode): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="training_modes[]" value="<?= e($mode) ?>" id="mode-<?= strtolower($mode) ?>" <?= in_array($mode, $trainingModesSelected, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mode-<?= strtolower($mode) ?>"><?= e($mode) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-12"><hr class="my-1"></div>
                <div class="col-12"><h6 class="fw-semibold mb-0">Social links</h6></div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-linkedin">LinkedIn</label>
                    <input id="profile-linkedin" type="url" name="linkedin_url" class="form-control" placeholder="https://" value="<?= e($institute['linkedin_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-instagram">Instagram</label>
                    <input id="profile-instagram" type="url" name="instagram_url" class="form-control" placeholder="https://" value="<?= e($institute['instagram_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-youtube">YouTube</label>
                    <input id="profile-youtube" type="url" name="youtube_url" class="form-control" placeholder="https://" value="<?= e($institute['youtube_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-facebook">Facebook</label>
                    <input id="profile-facebook" type="url" name="facebook_url" class="form-control" placeholder="https://" value="<?= e($institute['facebook_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="profile-twitter">Twitter / X</label>
                    <input id="profile-twitter" type="url" name="twitter_url" class="form-control" placeholder="https://" value="<?= e($institute['twitter_url'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save changes</button>
        </form>
    </div>
</div>

<!-- Faculty -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-workspace me-2 text-primary"></i>Faculty</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#facultyModal" onclick="resetResourceModal('facultyModal', '<?= url('/dashboard/institute/faculty') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($faculty)): ?>
            <p class="text-muted small mb-0">No faculty added yet.</p>
        <?php endif; ?>
        <?php foreach ($faculty as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start">
                <div>
                    <strong><?= e($row['name']) ?></strong><?= !empty($row['designation']) ? ' - ' . e($row['designation']) : '' ?><br>
                    <?php if (!empty($row['expertise'])): ?><span class="small text-muted"><?= e($row['expertise']) ?></span><?php endif; ?>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#facultyModal"
                        onclick='openResourceModal("facultyModal", "<?= url('/dashboard/institute/faculty/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/institute/faculty/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this faculty member?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Gallery -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-images me-2 text-primary"></i>Gallery</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#galleryModal">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($gallery)): ?>
            <p class="text-muted small mb-0">No photos added yet.</p>
        <?php endif; ?>
        <div class="row g-2">
            <?php foreach ($gallery as $row): ?>
                <div class="col-6 col-md-3">
                    <div class="position-relative">
                        <img src="<?= url($row['image_path']) ?>" class="rounded w-100" loading="lazy" style="height:100px;object-fit:cover;" alt="<?= e($row['caption'] ?? '') ?>">
                        <form method="post" action="<?= url('/dashboard/institute/gallery/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this photo?');" class="position-absolute top-0 end-0 m-1">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger py-0 px-1" aria-label="Delete photo<?= !empty($row['caption']) ? ': ' . e($row['caption']) : '' ?>"><i class="bi bi-x"></i></button>
                        </form>
                    </div>
                    <?php if (!empty($row['caption'])): ?><div class="small text-muted mt-1"><?= e($row['caption']) ?></div><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Certificates -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-award me-2 text-primary"></i>Accreditation &amp; Certificates</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#certificateModal" onclick="resetResourceModal('certificateModal', '<?= url('/dashboard/institute/certificates') ?>')">Add</button>
    </div>
    <div class="card-body">
        <?php if (empty($certificates)): ?>
            <p class="text-muted small mb-0">No certificates added yet.</p>
        <?php endif; ?>
        <?php foreach ($certificates as $row): ?>
            <div class="profile-row d-flex justify-content-between align-items-start">
                <div>
                    <strong><?= e($row['title']) ?></strong><br>
                    <span class="small text-muted"><?= e($row['issuing_body'] ?? '') ?><?= !empty($row['issued_year']) ? ' &middot; ' . e($row['issued_year']) : '' ?></span>
                    <?php if (!empty($row['document_path'])): ?> &middot; <a href="<?= url($row['document_path']) ?>" target="_blank" class="small">Document</a><?php endif; ?>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#certificateModal"
                        onclick='openResourceModal("certificateModal", "<?= url('/dashboard/institute/certificates/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                    <form method="post" action="<?= url('/dashboard/institute/certificates/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this certificate?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Placement wall -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Placement Wall</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#placementModal" onclick="resetResourceModal('placementModal', '<?= url('/dashboard/institute/placements') ?>')">Add</button>
    </div>
    <div class="card-body">
        <p class="text-muted small">These entries are institute-reported and shown publicly labeled as such - not verified by Road2Job. Recent, complete entries carry more weight in your discovery ranking.</p>
        <?php if (!empty($placements)): ?>
            <input type="search" id="placementSearchInput" class="form-control form-control-sm mb-3" placeholder="Search by student or company name...">
        <?php endif; ?>
        <?php if (empty($placements)): ?>
            <p class="text-muted small mb-0">No placement records added yet.</p>
        <?php endif; ?>
        <div id="placementList">
            <?php foreach ($placements as $row): ?>
                <div class="profile-row d-flex justify-content-between align-items-start placement-row" data-search="<?= e(strtolower($row['student_name'] . ' ' . $row['company_name'])) ?>">
                    <div class="d-flex gap-2">
                        <?php if (!empty($row['student_photo_path'])): ?>
                            <img src="<?= url($row['student_photo_path']) ?>" alt="" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                        <?php endif; ?>
                        <div>
                            <strong><?= e($row['student_name']) ?></strong> &mdash; <?= e($row['company_name']) ?>
                            <?php if (($row['status'] ?? 'active') !== 'active'): ?><span class="badge text-bg-secondary ms-1">Hidden by admin</span><?php endif; ?>
                            <br>
                            <span class="small text-muted">
                                <?php if (!empty($row['job_role'])): ?><?= e($row['job_role']) ?> &middot; <?php endif; ?>
                                <?php if (!empty($row['package_amount'])): ?>&#8377;<?= number_format((float) $row['package_amount']) ?>/yr &middot; <?php endif; ?>
                                <?= e($row['placement_year'] ?? '') ?>
                                <?php if (!empty($row['course_name'])): ?> &middot; <?= e($row['course_name']) ?><?php endif; ?>
                                <?php if (!empty($row['placement_type'])): ?> &middot; <?= e($row['placement_type']) ?><?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#placementModal"
                            onclick='openResourceModal("placementModal", "<?= url('/dashboard/institute/placements/' . $row['id']) ?>", <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                        <form method="post" action="<?= url('/dashboard/institute/placements/' . $row['id'] . '/delete') ?>" onsubmit="return confirm('Delete this record?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="h6 mb-1"><i class="bi bi-megaphone me-2 text-primary"></i>Institute Updates</h2>
            <p class="text-muted small mb-0">Post placement drives, admissions, workshops and achievements to your public feed.</p>
        </div>
        <a href="<?= url('/dashboard/institute/updates') ?>" class="btn btn-outline-primary btn-sm">Manage updates</a>
    </div>
</div>

<?php Core\View::partial('dashboard/institute/_profile_modals'); ?>

<script src="<?= asset('js/multi-select.js') ?>"></script>
<script src="<?= asset('js/profile.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var prefill = {
        specializationsRoot: <?= json_encode(array_values(array_filter(array_map('trim', explode(',', $institute['specializations'] ?? ''))))) ?>,
        facilitiesRoot: <?= json_encode(array_values(array_filter(array_map('trim', explode(',', $institute['facilities'] ?? ''))))) ?>
    };
    Object.keys(prefill).forEach(function (id) {
        var root = document.getElementById(id);
        if (root && typeof root.msPopulate === 'function') root.msPopulate(prefill[id]);
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('placementSearchInput');
    if (!searchInput) return;
    searchInput.addEventListener('input', function () {
        var q = searchInput.value.trim().toLowerCase();
        document.querySelectorAll('.placement-row').forEach(function (row) {
            row.style.display = row.dataset.search.indexOf(q) === -1 ? 'none' : '';
        });
    });
});
</script>
