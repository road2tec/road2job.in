<?php
Core\View::partial('partials/page_header', [
    'title' => 'Terms of Service',
    'subtitle' => 'Last updated: ' . date('F Y'),
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 820px;">
        <div class="alert alert-warning">
            <strong>Draft notice:</strong> this is a starting draft of the terms governing use of Road2Job, not final legal text. Please have it reviewed by a qualified professional before publishing it as your binding terms.
        </div>

        <h2 class="h5 fw-semibold mt-4">1. Accepting these terms</h2>
        <p>By creating an account or using Road2Job, you agree to these terms. If you don't agree, please don't use the platform.</p>

        <h2 class="h5 fw-semibold mt-4">2. Eligibility &amp; account roles</h2>
        <p>You can register as a Student, Employer, Recruiter, Training Institute, College, or Mentor. You're responsible for choosing the role that accurately describes you and for keeping your account information accurate.</p>

        <h2 class="h5 fw-semibold mt-4">3. Your account &amp; security</h2>
        <ul>
            <li>You're responsible for keeping your password confidential and for all activity under your account.</li>
            <li>We verify mobile numbers via OTP; providing a number you don't own, or sharing OTPs with others, is not permitted.</li>
            <li>We may suspend accounts showing signs of abuse, fraud, or repeated failed login attempts, to protect the platform and its users.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">4. Platform status</h2>
        <p>Road2Job is under active development and being rolled out in phases. Features described as upcoming or "coming soon" are not yet available, and functionality may change as new modules launch.</p>

        <h2 class="h5 fw-semibold mt-4">5. Acceptable use</h2>
        <p>You agree not to misuse the platform &mdash; including attempting to bypass security controls, submitting false information, or using the platform for any unlawful purpose.</p>

        <h2 class="h5 fw-semibold mt-4">6. Changes to these terms</h2>
        <p>We may update these terms as the platform evolves. Continued use after an update means you accept the revised terms.</p>

        <h2 class="h5 fw-semibold mt-4">7. Contact</h2>
        <p>Questions about these terms can be sent through our <a href="<?= url('/contact') ?>">Contact page</a>.</p>
    </div>
</section>
