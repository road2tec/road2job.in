<?php
Core\View::partial('partials/page_header', [
    'title' => 'About Road2Job',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 820px;">
        <p class="lead text-muted">Road2Job is being built as a single career and recruitment ecosystem for students, employers, training institutes and colleges &mdash; instead of scattering job boards, placement cells and course listings across separate, disconnected tools.</p>

        <h2 class="h4 fw-semibold mt-5 mb-3 font-display">Why we're building this</h2>
        <p>Job hunting and campus placements in India involve a lot of moving parts: students juggling resumes, applications and interview prep; employers screening applicants across scattered channels; training institutes trying to prove their placement record; and college placement cells coordinating drives across departments. Road2Job's goal is to bring all four of those journeys onto one platform, verified and role-aware from the first sign-up.</p>

        <h2 class="h4 fw-semibold mt-5 mb-3 font-display">Where we are today</h2>
        <p>We're in early access. Account registration, mobile OTP verification, role-based dashboards and account security are live. The rest &mdash; AI-assisted resume tools, job matching, mock interviews, institute and college modules &mdash; is being built and rolled out module by module, so features go live only once they're genuinely ready.</p>

        <h2 class="h4 fw-semibold mt-5 mb-3 font-display">Who it's for</h2>
        <div class="row g-3 mt-1">
            <div class="col-sm-6">
                <div class="card listing-card h-100"><div class="card-body d-flex gap-3 align-items-center"><div class="listing-card__logo-fallback"><i class="bi bi-mortarboard"></i></div><div><strong>Students &amp; freshers</strong> preparing for internships and placements.</div></div></div>
            </div>
            <div class="col-sm-6">
                <div class="card listing-card h-100"><div class="card-body d-flex gap-3 align-items-center"><div class="listing-card__logo-fallback"><i class="bi bi-briefcase"></i></div><div><strong>Employers &amp; recruiters</strong> looking to hire efficiently.</div></div></div>
            </div>
            <div class="col-sm-6">
                <div class="card listing-card h-100"><div class="card-body d-flex gap-3 align-items-center"><div class="listing-card__logo-fallback"><i class="bi bi-easel"></i></div><div><strong>Training institutes</strong> showcasing courses and outcomes.</div></div></div>
            </div>
            <div class="col-sm-6">
                <div class="card listing-card h-100"><div class="card-body d-flex gap-3 align-items-center"><div class="listing-card__logo-fallback"><i class="bi bi-bank"></i></div><div><strong>Colleges</strong> running placement cells and campus drives.</div></div></div>
            </div>
        </div>

        <div class="mt-5 text-center">
            <a href="<?= url('/register') ?>" class="btn btn-primary btn-lg">Join Road2Job</a>
        </div>
    </div>
</section>
