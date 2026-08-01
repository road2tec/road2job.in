<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="h4 mb-1 font-display">Verify your account</h1>
        <p class="text-muted small mb-4">Both steps below are required before you can log in.</p>

        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="feature-icon" style="width:2.25rem;height:2.25rem;font-size:1.1rem;">
                        <?php if (!empty($pendingUser['phone_verified_at'])): ?>
                            <i class="bi bi-check-lg text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-phone"></i>
                        <?php endif; ?>
                    </span>
                    <h2 class="h6 fw-semibold mb-0">1. Verify your mobile number</h2>
                </div>

                <?php if (!empty($pendingUser['phone_verified_at'])): ?>
                    <p class="text-success small mb-0 ps-1"><i class="bi bi-check-circle-fill me-1"></i>Phone verified.</p>
                <?php else: ?>
                    <p class="text-muted small">Enter the OTP sent to your registered mobile number.</p>
                    <form method="post" action="<?= url('/verify-otp') ?>" data-guard-submit>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <input type="text" name="otp" class="form-control" maxlength="6" inputmode="numeric" placeholder="6-digit OTP" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" data-loading-text="Verifying...">Verify OTP</button>
                    </form>
                    <form method="post" action="<?= url('/resend-otp') ?>" class="mt-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-link p-0 small">Resend OTP</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="feature-icon" style="width:2.25rem;height:2.25rem;font-size:1.1rem;">
                        <?php if (!empty($pendingUser['email_verified_at'])): ?>
                            <i class="bi bi-check-lg text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-envelope"></i>
                        <?php endif; ?>
                    </span>
                    <h2 class="h6 fw-semibold mb-0">2. Verify your email</h2>
                </div>

                <?php if (!empty($pendingUser['email_verified_at'])): ?>
                    <p class="text-success small mb-0 ps-1"><i class="bi bi-check-circle-fill me-1"></i>Email verified.</p>
                <?php else: ?>
                    <p class="text-muted small mb-2">We sent a verification link to <?= e($pendingUser['email'] ?? '') ?>. Click it to continue, or resend below.</p>
                    <form method="post" action="<?= url('/resend-verification-email') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-primary w-100">Resend verification email</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
