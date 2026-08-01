<?php
Core\View::partial('partials/page_header', [
    'title' => 'Privacy Policy',
    'subtitle' => 'Last updated: ' . date('F Y'),
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 820px;">
        <div class="alert alert-warning">
            <strong>Draft notice:</strong> this policy describes, in plain language, what Road2Job actually collects and how it is used today. It is a starting draft, not final legal text &mdash; please have it reviewed by a qualified professional (including for compliance with India's Digital Personal Data Protection Act, 2023) before relying on it as your published policy.
        </div>

        <h2 class="h5 fw-semibold mt-4">1. Information we collect</h2>
        <ul>
            <li>Account details you provide: full name, email address, mobile number, and role (student, employer, recruiter, training institute, college, or mentor).</li>
            <li>Verification data: a one-time password sent to your mobile number to confirm you own it.</li>
            <li>Login &amp; security data: password (stored as a one-way hash, never in plain text), login timestamps, IP address, device/browser information, and login history, used to protect your account.</li>
            <li>Anything you submit through forms, such as the Contact page (name, email, subject, message).</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">2. How we use it</h2>
        <ul>
            <li>To create and secure your account, verify your identity, and let you sign in.</li>
            <li>To operate role-specific features (e.g. a student dashboard vs. an employer dashboard).</li>
            <li>To detect and prevent suspicious login activity (repeated failed attempts, unusual devices).</li>
            <li>To respond to messages you send us.</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">3. What we don't do</h2>
        <ul>
            <li>We don't sell your personal data.</li>
            <li>We don't share your mobile number or email with other users without your action (e.g. applying to a job once that feature is live).</li>
        </ul>

        <h2 class="h5 fw-semibold mt-4">4. Cookies &amp; sessions</h2>
        <p>We use a single secure, HTTP-only session cookie to keep you signed in. It is required for the platform to function and is not used for third-party advertising or tracking.</p>

        <h2 class="h5 fw-semibold mt-4">5. Data retention</h2>
        <p>We retain account data for as long as your account is active. You may request deletion of your account and associated data by contacting us.</p>

        <h2 class="h5 fw-semibold mt-4">6. Your rights</h2>
        <p>You can request access to, correction of, or deletion of your personal data at any time via our <a href="<?= url('/contact') ?>">Contact page</a>.</p>

        <h2 class="h5 fw-semibold mt-4">7. Contact us</h2>
        <p>Questions about this policy can be sent through our <a href="<?= url('/contact') ?>">Contact page</a>.</p>
    </div>
</section>
