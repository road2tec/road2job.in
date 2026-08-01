<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-1 font-display">Reset password</h1>
                <p class="text-muted small mb-4">Choose a new password for your account.</p>

                <form method="post" action="<?= url('/reset-password') ?>" data-guard-submit>
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <input type="hidden" name="email" value="<?= e($email) ?>">

                    <div class="mb-3">
                        <label class="form-label" for="resetPassword">New password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="resetPassword" class="form-control" minlength="8" required autofocus>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#resetPassword" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">At least 8 characters.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="resetPasswordConfirm">Confirm new password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="resetPasswordConfirm" class="form-control" minlength="8" required data-match-target="#resetPassword" data-match-feedback="#resetMatchFeedback">
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#resetPasswordConfirm" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="resetMatchFeedback" class="form-text"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" data-loading-text="Resetting...">Reset password</button>
                </form>
            </div>
        </div>
    </div>
</div>
