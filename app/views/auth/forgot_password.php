<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-1 font-display">Forgot password</h1>
                <p class="text-muted small mb-4">Enter your email and we'll send you a reset link.</p>

                <form method="post" action="<?= url('/forgot-password') ?>" data-guard-submit>
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label" for="forgot-password-email">Email</label>
                        <input id="forgot-password-email" type="email" name="email" class="form-control" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" data-loading-text="Sending...">Send reset link</button>
                </form>

                <p class="text-center mt-4 small"><a href="<?= url('/login') ?>">&larr; Back to login</a></p>
            </div>
        </div>
    </div>
</div>
