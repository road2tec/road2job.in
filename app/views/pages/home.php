<section class="hero-section py-5 py-lg-6">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge glass-card px-3 py-2 mb-3">🚀 Early Access is live</span>
                <h1 class="display-5 fw-bold mb-3 font-display">One platform for every step of your career journey.</h1>
                <p class="lead mb-4" style="opacity: 0.92;">
                    Road2Job connects students, employers, training institutes and colleges &mdash; from a secure,
                    OTP-verified account today to AI-powered matching, resume tools and campus placements as we roll them out.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= url('/register') ?>" class="btn btn-light btn-lg fw-semibold">Create your free account</a>
                    <a href="#roadmap" class="btn btn-outline-light btn-lg">See what's coming</a>
                </div>
                <p class="small mt-3 mb-0" style="opacity: 0.8;">Free to join &middot; No credit card required</p>
            </div>
            <div class="col-lg-5">
                <div class="text-uppercase small fw-semibold mb-3" style="opacity: .75; letter-spacing: .06em;">Built for every persona</div>
                <div class="persona-cluster">
                    <div class="persona-card persona-card--1">
                        <span class="persona-card__icon"><i class="bi bi-mortarboard"></i></span>
                        <span class="persona-card__label">Students building a career</span>
                    </div>
                    <div class="persona-card persona-card--2">
                        <span class="persona-card__icon"><i class="bi bi-briefcase"></i></span>
                        <span class="persona-card__label">Employers hiring talent</span>
                    </div>
                    <div class="persona-card persona-card--3">
                        <span class="persona-card__icon"><i class="bi bi-easel"></i></span>
                        <span class="persona-card__label">Institutes showcasing placements</span>
                    </div>
                    <div class="persona-card persona-card--4">
                        <span class="persona-card__icon"><i class="bi bi-bank"></i></span>
                        <span class="persona-card__label">Colleges running campus drives</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 py-lg-6">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <h2 class="fw-bold font-display">Choose your path</h2>
            <p class="text-muted">Every account is tailored to what you're here to do.</p>
        </div>
        <div class="row g-4">
            <?php
            $roleTiles = [
                ['slug' => 'student', 'icon' => 'bi-mortarboard', 'title' => 'Students', 'text' => 'Create a verified profile, build your resume, and get ready for internships and placements.'],
                ['slug' => 'employer', 'icon' => 'bi-briefcase', 'title' => 'Employers', 'text' => 'Set up your company profile and get ready to post roles and manage applicants in one place.'],
                ['slug' => 'institute', 'icon' => 'bi-easel', 'title' => 'Training Institutes', 'text' => 'Bring your courses, faculty and placement record onto a platform students already trust.'],
                ['slug' => 'college', 'icon' => 'bi-bank', 'title' => 'Colleges', 'text' => 'Prepare your placement cell for campus drives, department stats and alumni tracking.'],
            ];
            ?>
            <?php foreach ($roleTiles as $tile): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="role-card p-4 reveal">
                        <div class="feature-icon mb-3"><i class="bi <?= e($tile['icon']) ?>"></i></div>
                        <h3 class="h5 fw-semibold"><?= e($tile['title']) ?></h3>
                        <p class="text-muted small"><?= e($tile['text']) ?></p>
                        <a href="<?= url('/register?role=' . $tile['slug']) ?>" class="stretched-link fw-semibold small">Get started &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 py-lg-6">
    <div class="container">
        <div class="text-center mb-4 reveal">
            <h2 class="fw-bold font-display">Find your next opportunity</h2>
            <p class="text-muted">Search live roles, or jump straight into a category.</p>
        </div>

        <form method="get" action="<?= url('/jobs') ?>" class="search-panel mb-4 reveal" style="max-width: 640px; margin-inline: auto;">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm">
                    <label class="form-label" for="home-keyword">Keyword</label>
                    <div class="input-icon">
                        <i class="bi bi-search"></i>
                        <input id="home-keyword" type="text" name="keyword" class="form-control" placeholder="Job title, skill, company...">
                    </div>
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="submit" class="btn btn-primary btn-search w-100">
                        <i class="bi bi-search me-1"></i>Search jobs
                    </button>
                </div>
            </div>
        </form>

        <div class="d-flex flex-wrap justify-content-center gap-2 mb-5 reveal">
            <a href="<?= url('/jobs?type=full_time') ?>" class="filter-chip">Full-time</a>
            <a href="<?= url('/jobs?type=internship') ?>" class="filter-chip">Internships</a>
            <a href="<?= url('/jobs?type=part_time') ?>" class="filter-chip">Part-time</a>
            <a href="<?= url('/jobs?is_remote=1') ?>" class="filter-chip">Remote</a>
            <a href="<?= url('/jobs?experience_level=fresher') ?>" class="filter-chip">Fresher-friendly</a>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4 reveal">
            <h3 class="h5 fw-semibold mb-0 font-display">Latest opportunities</h3>
            <a href="<?= url('/jobs') ?>" class="small fw-semibold">Browse all jobs &rarr;</a>
        </div>

        <?php if (empty($latestJobs)): ?>
            <div class="empty-state reveal">
                <div class="empty-state__icon"><i class="bi bi-briefcase"></i></div>
                <h3 class="fw-semibold">No roles published yet</h3>
                <p class="text-muted mb-0">Employers are setting up - real openings will appear here the moment the first role goes live.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($latestJobs as $job): ?>
                    <div class="col-md-6 reveal">
                        <a href="<?= url('/jobs/' . $job['id']) ?>" class="card listing-card text-decoration-none text-reset h-100">
                            <div class="card-body d-flex gap-3 align-items-start">
                                <?php if (!empty($job['company_logo_path'])): ?>
                                    <img src="<?= url($job['company_logo_path']) ?>" alt="<?= e($job['company_name']) ?>" class="listing-card__logo" loading="lazy">
                                <?php else: ?>
                                    <div class="listing-card__logo-fallback"><i class="bi bi-building"></i></div>
                                <?php endif; ?>
                                <div class="flex-fill">
                                    <h4 class="h6 fw-semibold mb-1"><?= e($job['title']) ?></h4>
                                    <div class="small text-muted mb-1"><?= e($job['company_name']) ?></div>
                                    <div class="small text-muted">
                                        <i class="bi bi-briefcase me-1"></i><?= e(ucfirst(str_replace('_', ' ', $job['type']))) ?>
                                        <?php if (!empty($job['location'])): ?><span class="mx-1">&middot;</span><i class="bi bi-geo-alt me-1"></i><?= e($job['location']) ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-5 py-lg-6 section-tint" id="roadmap">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-6 reveal">
                <h2 class="fw-bold mb-4 font-display"><i class="bi bi-check-circle-fill text-success me-2"></i>Available in Early Access</h2>
                <div class="roadmap-card">
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="d-flex gap-3 align-items-start"><span class="roadmap-check"><i class="bi bi-check2"></i></span><span>Secure sign-up with mobile OTP verification</span></li>
                        <li class="d-flex gap-3 align-items-start"><span class="roadmap-check"><i class="bi bi-check2"></i></span><span>Role-based accounts &amp; dashboards for every persona</span></li>
                        <li class="d-flex gap-3 align-items-start"><span class="roadmap-check"><i class="bi bi-check2"></i></span><span>Login history, device tracking &amp; lockout protection</span></li>
                        <li class="d-flex gap-3 align-items-start"><span class="roadmap-check"><i class="bi bi-check2"></i></span><span>Email-based password recovery</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 reveal">
                <h2 class="fw-bold mb-4 font-display"><i class="bi bi-signpost-2-fill text-primary me-2"></i>Coming next</h2>
                <div class="row g-3">
                    <?php
                    $roadmap = [
                        ['icon' => 'bi-magic', 'text' => 'AI-powered resume parsing &amp; ATS score'],
                        ['icon' => 'bi-people', 'text' => 'Smart job &amp; internship matching'],
                        ['icon' => 'bi-patch-check', 'text' => 'Verified employer &amp; institute network'],
                        ['icon' => 'bi-camera-video', 'text' => 'Mock interviews &amp; skill assessments'],
                        ['icon' => 'bi-globe', 'text' => 'Personal portfolio websites (road2job.in/u/you)'],
                        ['icon' => 'bi-bar-chart', 'text' => 'Campus placement management for colleges'],
                    ];
                    ?>
                    <?php foreach ($roadmap as $item): ?>
                        <div class="col-sm-6">
                            <div class="border rounded-3 p-3 h-100 roadmap-tile">
                                <i class="bi <?= e($item['icon']) ?> text-primary mb-2 d-block fs-5"></i>
                                <span class="small"><?= $item['text'] ?></span>
                                <span class="badge text-bg-light border badge-coming-soon ms-1">Soon</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 py-lg-6">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <h2 class="fw-bold font-display">How it will work</h2>
            <p class="text-muted">Get set up today, and be first in line as each module rolls out.</p>
        </div>
        <div class="row g-4 steps-row">
            <?php
            $steps = [
                ['n' => 1, 'title' => 'Sign up &amp; verify', 'text' => 'Register with your role and verify your mobile number via OTP.'],
                ['n' => 2, 'title' => 'Build your profile', 'text' => 'Add your details so your dashboard is ready when each module launches.'],
                ['n' => 3, 'title' => 'Grow with the platform', 'text' => 'Get matched, hire, or manage placements as we roll out each phase.'],
            ];
            ?>
            <?php foreach ($steps as $step): ?>
                <div class="col-md-4">
                    <div class="text-center reveal">
                        <div class="step-number mx-auto mb-3"><?= (int) $step['n'] ?></div>
                        <h3 class="h6 fw-semibold"><?= $step['title'] ?></h3>
                        <p class="text-muted small"><?= $step['text'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="cta-section p-5 text-center reveal">
            <h2 class="fw-bold mb-3 font-display">Be part of it from day one.</h2>
            <p class="mb-4" style="opacity: 0.9;">Create your account now and grow with the platform as new features ship.</p>
            <a href="<?= url('/register') ?>" class="btn btn-light btn-lg fw-semibold">Create your free account</a>
        </div>
    </div>
</section>
