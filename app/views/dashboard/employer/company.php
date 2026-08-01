<h1 class="h4 mb-1">Company Profile</h1>
<p class="text-muted mb-4">This information appears on your job postings once they're published.</p>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-building me-2 text-primary"></i>Company details</div>
    <div class="card-body">
        <form method="post" action="<?= url('/dashboard/company') ?>" enctype="multipart/form-data" data-guard-submit>
            <?= csrf_field() ?>

            <div class="row g-3 mb-3 align-items-center">
                <div class="col-auto">
                    <?php if (!empty($company['logo_path'])): ?>
                        <img src="<?= url($company['logo_path']) ?>" alt="Logo" class="rounded border" loading="lazy" style="width:64px;height:64px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded d-flex align-items-center justify-content-center feature-icon" style="width:64px;height:64px;">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <label class="form-label" for="company-logo">Company logo</label>
                    <input id="company-logo" type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="company-name">Company name</label>
                    <input id="company-name" type="text" name="name" class="form-control" value="<?= e($company['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="company-industry">Industry</label>
                    <input id="company-industry" type="text" name="industry" class="form-control" placeholder="e.g. Information Technology" value="<?= e($company['industry'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="company-company-size">Company size</label>
                    <select id="company-company-size" name="company_size" class="form-select">
                        <option value="">Select size</option>
                        <?php foreach (['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'] as $size): ?>
                            <option value="<?= $size ?>" <?= ($company['company_size'] ?? '') === $size ? 'selected' : '' ?>><?= $size ?> employees</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="company-founded-year">Founded year</label>
                    <input id="company-founded-year" type="number" name="founded_year" class="form-control" min="1800" max="<?= date('Y') ?>" value="<?= e($company['founded_year'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="company-website">Website</label>
                    <input id="company-website" type="url" name="website" class="form-control" placeholder="https://" value="<?= e($company['website'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="company-headquarters-location">Headquarters</label>
                    <input id="company-headquarters-location" type="text" name="headquarters_location" class="form-control" placeholder="City, Country" value="<?= e($company['headquarters_location'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="company-description">About the company</label>
                    <textarea id="company-description" name="description" class="form-control" rows="4"><?= e($company['description'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save changes</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-patch-check me-2 text-primary"></i>Verification</div>
    <div class="card-body">
        <?php $status = $company['verification_status'] ?? 'unverified'; ?>
        <?php $statusBadge = [
            'unverified' => 'text-bg-secondary',
            'pending' => 'text-bg-warning',
            'verified' => 'text-bg-success',
            'rejected' => 'text-bg-danger',
        ][$status] ?? 'text-bg-secondary'; ?>

        <p><span class="badge <?= $statusBadge ?>"><?= e(ucfirst($status)) ?></span></p>

        <?php if (in_array($status, ['unverified', 'rejected'], true)): ?>
            <p class="text-muted small">Submit a verification request so students can see your company is genuine. A document (registration certificate, GST, etc.) is optional but speeds up review.</p>
            <form method="post" action="<?= url('/dashboard/company/verification') ?>" enctype="multipart/form-data" class="row g-2 align-items-end" data-guard-submit>
                <?= csrf_field() ?>
                <div class="col-auto">
                    <label class="form-label" for="company-verification-document">Verification document (optional)</label>
                    <input id="company-verification-document" type="file" name="verification_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Submit for verification</button>
                </div>
            </form>
        <?php elseif ($status === 'pending'): ?>
            <p class="text-muted small mb-0">Your verification request is under review.</p>
        <?php else: ?>
            <p class="text-muted small mb-0">Your company is verified.</p>
        <?php endif; ?>
    </div>
</div>
