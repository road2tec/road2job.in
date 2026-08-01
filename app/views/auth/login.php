<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-1 font-display">Welcome back</h1>
                <p class="text-muted small mb-4">Log in to your Road2Job account.</p>

                <?php if (is_authenticated()): $current = auth(); ?>
                    <div class="alert alert-info small mb-4">
                        You're currently logged in as <strong><?= e($current['full_name']) ?></strong> (<?= e($current['email']) ?>).
                        <a href="<?= url('/dashboard') ?>">Continue to your dashboard</a>, or log in below with different credentials to switch accounts.
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= url('/login') ?>" data-guard-submit>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="login-identifier">Email or Mobile Number</label>
                        <input id="login-identifier" type="text" name="identifier" class="form-control" value="<?= old('identifier') ?>" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="loginPassword">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="loginPassword" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#loginPassword" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="remember" value="1" class="form-check-input" id="rememberMe">
                        <label class="form-check-label small" for="rememberMe">Remember me on this device</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" data-loading-text="Logging in...">Login</button>
                </form>

                <div class="d-flex justify-content-between mt-4 small">
                    <a href="<?= url('/forgot-password') ?>">Forgot password?</a>
                    <a href="<?= url('/register') ?>">Create account</a>
                </div>
            </div>
        </div>
    </div>
</div>
