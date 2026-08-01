<?php
$roleIcons = [
    'student' => 'bi-mortarboard',
    'employer' => 'bi-briefcase',
    'recruiter' => 'bi-person-badge',
    'institute' => 'bi-easel',
    'college' => 'bi-bank',
    'mentor' => 'bi-people',
];
$chosenRole = old('role') !== '' ? old('role') : ($selectedRole ?? '');
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-1 font-display">Create your Road2Job account</h1>
                <p class="text-muted small mb-4">Free to join - pick the account type that fits you.</p>

                <form method="post" action="<?= url('/register') ?>" data-guard-submit>
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label">I am a</label>
                        <div class="row g-2">
                            <?php foreach ($roles as $slug => $roleMeta): ?>
                                <?php if ($roleMeta['self_registerable']): ?>
                                    <div class="col-6 col-md-4">
                                        <label class="role-choice <?= $chosenRole === $slug ? 'role-choice--active' : '' ?>">
                                            <input type="radio" name="role" value="<?= e($slug) ?>" <?= $chosenRole === $slug ? 'checked' : '' ?> required>
                                            <i class="bi <?= e($roleIcons[$slug] ?? 'bi-person') ?>"></i>
                                            <span><?= e($roleMeta['label']) ?></span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="register-full-name">Full name</label>
                            <input id="register-full-name" type="text" name="full_name" class="form-control" value="<?= old('full_name') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="register-email">Email</label>
                            <input id="register-email" type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label" for="register-phone">Mobile number</label>
                        <input id="register-phone" type="text" name="phone" class="form-control" value="<?= old('phone') ?>" maxlength="10" required>
                        <?php if ($requireOtp): ?>
                            <div class="form-text">We'll send an OTP to this number via SMS to verify your account.</div>
                        <?php else: ?>
                            <div class="form-text">Used for account recovery and important updates.</div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="registerPassword">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="registerPassword" class="form-control" minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#registerPassword" aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">At least 8 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="registerPasswordConfirm">Confirm password</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="registerPasswordConfirm" class="form-control" minlength="8" required data-match-target="#registerPassword" data-match-feedback="#passwordMatchFeedback">
                                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#registerPasswordConfirm" aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatchFeedback" class="form-text"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4" data-loading-text="Creating account...">Register</button>
                </form>

                <p class="text-center mt-4 small">Already have an account? <a href="<?= url('/login') ?>">Login</a></p>
            </div>
        </div>
    </div>
</div>
