<footer class="site-footer pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="<?= url('/') ?>" class="footer-brand text-decoration-none fw-bold fs-5 mb-2 d-inline-flex">
                    <img src="<?= asset('img/logo-icon-64.png') ?>" alt="" width="32" height="32">
                    Road2Job
                </a>
                <p class="small mb-0 mt-2" style="opacity: .7;">AI Powered Career, Placement &amp; Recruitment Ecosystem for students, employers, training institutes and colleges.</p>
            </div>

            <div class="col-6 col-lg-2">
                <div class="fw-semibold mb-2 small text-uppercase">Company</div>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a class="footer-link" href="<?= url('/about') ?>">About</a></li>
                    <li><a class="footer-link" href="<?= url('/careers') ?>">Careers</a></li>
                    <li><a class="footer-link" href="<?= url('/contact') ?>">Contact</a></li>
                    <li><a class="footer-link" href="<?= url('/blog') ?>">Blog</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <div class="fw-semibold mb-2 small text-uppercase">Explore</div>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a class="footer-link" href="<?= url('/jobs') ?>">Jobs</a></li>
                    <li><a class="footer-link" href="<?= url('/internships') ?>">Internships</a></li>
                    <li><a class="footer-link" href="<?= url('/institutes') ?>">Training Institutes</a></li>
                    <li><a class="footer-link" href="<?= url('/colleges') ?>">Colleges</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-3">
                <div class="fw-semibold mb-2 small text-uppercase">Legal</div>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a class="footer-link" href="<?= url('/faq') ?>">FAQ</a></li>
                    <li><a class="footer-link" href="<?= url('/privacy-policy') ?>">Privacy Policy</a></li>
                    <li><a class="footer-link" href="<?= url('/terms-of-service') ?>">Terms of Service</a></li>
                </ul>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small" style="opacity: .65;">
            <span>&copy; <?= date('Y') ?> Road2Job. All rights reserved.</span>
            <span>Built for students, employers, institutes &amp; colleges.</span>
        </div>
    </div>
</footer>
