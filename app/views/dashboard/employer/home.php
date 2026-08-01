<h1 class="h4 mb-1">Welcome back, <?= e($user['full_name']) ?></h1>
<p class="text-muted mb-4">You're signed in as <strong><?= e($user['role_label']) ?></strong>.</p>

<?php if ($user['role_slug'] === 'employer'): ?>
    <?php
    $status = $company['verification_status'] ?? 'unverified';
    $statusBadge = [
        'unverified' => 'text-bg-secondary',
        'pending' => 'text-bg-warning',
        'verified' => 'text-bg-success',
        'rejected' => 'text-bg-danger',
    ][$status] ?? 'text-bg-secondary';
    ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Company</div>
                    <div class="h6 mb-0"><?= e($company['name'] ?? 'Not set up yet') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Verification</div>
                    <span class="badge <?= $statusBadge ?>"><?= e(ucfirst($status)) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Published jobs</div>
                    <div class="h6 mb-0"><?= (int) $publishedJobCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php
        $quickLinks = [
            ['title' => 'Company profile', 'text' => 'Update your details, logo and verification.', 'icon' => 'bi-building', 'href' => '/dashboard/company'],
            ['title' => 'Job postings', 'text' => 'Create and manage your job and internship listings.', 'icon' => 'bi-briefcase', 'href' => '/dashboard/jobs'],
            ['title' => 'Account security', 'text' => 'Review active sessions and login history.', 'icon' => 'bi-shield-lock', 'href' => '/account/security'],
        ];
        ?>
        <?php foreach ($quickLinks as $link): ?>
            <div class="col-md-4">
                <a href="<?= url($link['href']) ?>" class="card h-100 text-decoration-none text-reset">
                    <div class="card-body">
                        <div class="feature-icon mb-3"><i class="bi <?= e($link['icon']) ?>"></i></div>
                        <h2 class="h6 fw-semibold mb-1"><?= e($link['title']) ?></h2>
                        <p class="text-muted small mb-0"><?= e($link['text']) ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif ($user['role_slug'] === 'institute'): ?>
    <?php if ($institute !== null): ?>
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1">Profile completion</div>
                        <div class="h6 mb-0"><?= (int) $profileCompletionPercent ?>%</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1">Discovery rank</div>
                        <div class="h6 mb-0">#<?= (int) $rankPosition ?> of <?= (int) $activeInstituteCount ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1">Placements</div>
                        <div class="h6 mb-0"><?= (int) $totalPlacementCount ?> <span class="small text-muted fw-normal">(<?= (int) $recentPlacementCount ?> this week)</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1">Updates posted</div>
                        <div class="h6 mb-0"><?= (int) $totalUpdateCount ?> <span class="small text-muted fw-normal">(<?= (int) $recentUpdateCount ?> this week)</span></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Institute</div>
                    <div class="h6 mb-0"><?= e($institute['name'] ?? 'Not set up yet') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Published courses</div>
                    <div class="h6 mb-0"><?= (int) $publishedCourseCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Pending enrollment requests</div>
                    <div class="h6 mb-0"><?= (int) $pendingEnrollmentCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php
        $quickLinks = [
            ['title' => 'Institute profile', 'text' => 'Update your details, facilities and specializations.', 'icon' => 'bi-easel', 'href' => '/dashboard/institute'],
            ['title' => 'Courses', 'text' => 'Create and manage your course listings.', 'icon' => 'bi-mortarboard', 'href' => '/dashboard/institute/courses'],
            ['title' => 'Add placement', 'text' => 'Record a genuine student placement.', 'icon' => 'bi-graph-up-arrow', 'href' => '/dashboard/institute'],
            ['title' => 'Add update', 'text' => 'Post placement drives, admissions or events.', 'icon' => 'bi-megaphone', 'href' => '/dashboard/institute/updates'],
            ['title' => 'Enrollment requests', 'text' => 'Review students interested in your courses.', 'icon' => 'bi-person-check', 'href' => '/dashboard/institute/enrollments'],
        ];
        if ($portfolioUrl !== null) {
            $quickLinks[] = ['title' => 'View public portfolio', 'text' => 'See your institute page as students see it.', 'icon' => 'bi-box-arrow-up-right', 'href' => null, 'external' => $portfolioUrl];
        }
        ?>
        <?php foreach ($quickLinks as $link): ?>
            <div class="col-md-4">
                <a href="<?= $link['href'] !== null ? url($link['href']) : e($link['external']) ?>" <?= isset($link['external']) ? 'target="_blank" rel="noopener"' : '' ?> class="card h-100 text-decoration-none text-reset">
                    <div class="card-body">
                        <div class="feature-icon mb-3"><i class="bi <?= e($link['icon']) ?>"></i></div>
                        <h2 class="h6 fw-semibold mb-1"><?= e($link['title']) ?></h2>
                        <p class="text-muted small mb-0"><?= e($link['text']) ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif ($user['role_slug'] === 'college'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">College</div>
                    <div class="h6 mb-0"><?= e($college['name'] ?? 'Not set up yet') ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Published drives</div>
                    <div class="h6 mb-0"><?= (int) $publishedDriveCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">Pending registrations</div>
                    <div class="h6 mb-0"><?= (int) $pendingRegistrationCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php
        $quickLinks = [
            ['title' => 'College profile', 'text' => 'Update your details, department stats and alumni wall.', 'icon' => 'bi-bank', 'href' => '/dashboard/college'],
            ['title' => 'Campus drives', 'text' => 'Create and manage your campus drive listings.', 'icon' => 'bi-briefcase', 'href' => '/dashboard/college/drives'],
            ['title' => 'Registrations', 'text' => 'Review students interested in your drives.', 'icon' => 'bi-person-check', 'href' => '/dashboard/college/registrations'],
        ];
        ?>
        <?php foreach ($quickLinks as $link): ?>
            <div class="col-md-4">
                <a href="<?= url($link['href']) ?>" class="card h-100 text-decoration-none text-reset">
                    <div class="card-body">
                        <div class="feature-icon mb-3"><i class="bi <?= e($link['icon']) ?>"></i></div>
                        <h2 class="h6 fw-semibold mb-1"><?= e($link['title']) ?></h2>
                        <p class="text-muted small mb-0"><?= e($link['text']) ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="feature-icon mb-3"><i class="bi bi-building"></i></div>
            <h2 class="h6 fw-semibold mb-2">Tools for <?= e($user['role_label']) ?> accounts are on the way</h2>
            <p class="text-muted small mb-0">Account security and RBAC are already live - check <a href="<?= url('/account/security') ?>">Security</a> for active sessions and login history.</p>
        </div>
    </div>
<?php endif; ?>
