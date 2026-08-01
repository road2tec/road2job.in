<nav class="navbar navbar-expand-xl site-nav sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('/') ?>">
            <img src="<?= asset('img/logo-icon-64.png') ?>" alt="" width="34" height="34">
            Road2Job
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavCollapse" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="siteNavCollapse">
            <ul class="navbar-nav mx-auto gap-xl-2">
                <li class="nav-item"><a class="nav-link <?= is_active_path('/jobs') ? 'active' : '' ?>" href="<?= url('/jobs') ?>">Jobs</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/community') ? 'active' : '' ?>" href="<?= url('/community') ?>">Community</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/mentors') ? 'active' : '' ?>" href="<?= url('/mentors') ?>">Mentors</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/research-hub') ? 'active' : '' ?>" href="<?= url('/research-hub') ?>">Research Hub</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/roadmaps') ? 'active' : '' ?>" href="<?= url('/roadmaps') ?>">Roadmaps</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/events') ? 'active' : '' ?>" href="<?= url('/events') ?>">Events</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/about') ? 'active' : '' ?>" href="<?= url('/about') ?>">About</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/blog') ? 'active' : '' ?>" href="<?= url('/blog') ?>">Blog</a></li>
                <li class="nav-item"><a class="nav-link <?= is_active_path('/contact') ? 'active' : '' ?>" href="<?= url('/contact') ?>">Contact</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 mt-xl-0">
                <div class="form-check form-switch mb-0 me-1">
                    <input class="form-check-input" type="checkbox" id="themeToggle">
                    <label class="form-check-label small" for="themeToggle">Dark</label>
                </div>
                <a href="<?= url('/login') ?>" class="btn btn-outline-primary btn-sm">Login</a>
                <a href="<?= url('/register') ?>" class="btn btn-primary btn-sm">Get Started</a>
            </div>
        </div>
    </div>
</nav>
