<?php
Core\View::partial('partials/page_header', [
    'title' => 'Frequently Asked Questions',
    'subtitle' => 'Answers to common questions about Road2Job.',
]);
?>

<section class="py-5">
    <div class="container" style="max-width: 820px;">
        <?php
        $faqs = [
            ['q' => 'Is Road2Job free to join?', 'a' => 'Yes. Creating an account as a student, employer, training institute or college is free.'],
            ['q' => 'Why do I need to verify my mobile number?', 'a' => 'OTP verification confirms you own the number you registered with, which keeps accounts genuine and helps prevent spam and fraudulent listings across the platform.'],
            ['q' => "I didn't receive my OTP. What should I do?", 'a' => "Use the \"Resend OTP\" option on the verification page. If it still doesn't arrive, double-check the mobile number you registered with and try again in a few minutes."],
            ['q' => 'What can I do on Road2Job right now?', 'a' => "You can register, verify your account, log in, and access your role-specific dashboard. We're actively rolling out resume tools, job matching, mock interviews and institute/college modules — see our roadmap on the homepage."],
            ['q' => 'What roles can I sign up as?', 'a' => 'Student, Employer, Recruiter, Training Institute, College, or Mentor. Admin accounts are created internally and are not available through public registration.'],
            ['q' => 'How is my data kept secure?', 'a' => 'Passwords are hashed, sessions use secure cookies, every form is CSRF-protected, and repeated failed logins trigger a temporary lockout. See our Privacy Policy for full details.'],
            ['q' => 'How do I reset my password?', 'a' => 'Use "Forgot password" on the login page. We\'ll email a reset link to the address on your account.'],
            ['q' => 'How can I get in touch?', 'a' => 'Use our Contact page and we will get back to you as soon as we can.'],
        ];
        ?>

        <div class="accordion reveal" id="faqAccordion">
            <?php foreach ($faqs as $i => $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                            <?= e($faq['q']) ?>
                        </button>
                    </h2>
                    <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted"><?= e($faq['a']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="text-center text-muted mt-4">Still have questions? <a href="<?= url('/contact') ?>">Contact us</a>.</p>
    </div>
</section>
