<?php

namespace App\Controllers;

use App\Models\PasswordReset;
use App\Models\User;
use App\Services\AuthService;
use App\Services\MailService;
use App\Services\RateLimiter;
use App\Services\RememberMeService;
use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Validator;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showRegister(Request $request): void
    {
        $this->view('auth/register', [
            'roles' => config('roles'),
            'selectedRole' => (string) $request->input('role', ''),
            'requireOtp' => (bool) config('auth.require_otp', true),
        ], 'guest');
    }

    public function register(Request $request): void
    {
        $data = $request->only(['role', 'full_name', 'email', 'phone', 'password', 'password_confirmation']);

        $validator = Validator::make($data);
        $validator->validate([
            'role' => 'required',
            'full_name' => 'required|min:2|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|phone|unique:users,phone',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('_old', $data);
            $this->redirect('/register');
            return;
        }

        $data['ip'] = $request->ip();

        try {
            $result = $this->authService->register($data);
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/register');
            return;
        }

        if ($result['auto_activated']) {
            Session::flash('success', 'Account created and activated. You can now log in.');
            $this->redirect('/login');
            return;
        }

        Session::put('_pending_user_id', $result['user_id']);
        Session::flash('success', "Account created. We've sent an OTP to your phone and a verification link to your email - both are required to activate your account.");
        $this->redirect('/verify-otp');
    }

    public function showVerifyOtp(Request $request): void
    {
        if (!Session::has('_pending_user_id')) {
            $this->redirect('/register');
            return;
        }

        $user = User::find((int) Session::get('_pending_user_id'));

        $this->view('auth/verify_otp', ['pendingUser' => $user], 'guest');
    }

    public function verifyOtp(Request $request): void
    {
        $userId = Session::get('_pending_user_id');

        if ($userId === null) {
            $this->redirect('/register');
            return;
        }

        $code = trim((string) $request->input('otp', ''));

        if ($this->authService->verifyOtp((int) $userId, $code)) {
            $this->flashVerificationProgress((int) $userId);
            $this->redirect('/verify-otp');
            return;
        }

        Session::flash('error', 'Invalid or expired OTP. Please try again.');
        $this->redirect('/verify-otp');
    }

    public function resendOtp(Request $request): void
    {
        $userId = Session::get('_pending_user_id');

        if ($userId === null) {
            $this->redirect('/register');
            return;
        }

        $user = User::find((int) $userId);

        if ($user !== null) {
            $rateLimitKey = 'otp_resend:' . $user['phone'];

            if (RateLimiter::tooManyAttempts($rateLimitKey, 3, 10)) {
                Session::flash('error', 'Please wait a few minutes before requesting another code.');
                $this->redirect('/verify-otp');
                return;
            }

            RateLimiter::hit($rateLimitKey, $request->ip());
            $this->authService->resendOtp((int) $userId, $user['phone']);
        }

        Session::flash('success', 'A new OTP has been sent.');
        $this->redirect('/verify-otp');
    }

    public function verifyEmail(Request $request): void
    {
        $email = (string) $request->input('email', '');
        $token = (string) $request->input('token', '');

        if (!$this->authService->verifyEmail($email, $token)) {
            Session::flash('error', 'This verification link is invalid or has expired.');
            $this->redirect('/login');
            return;
        }

        $user = User::findByEmail($email);

        if ($user !== null && (int) Session::get('_pending_user_id') === (int) $user['id']) {
            $this->flashVerificationProgress((int) $user['id']);
            $this->redirect('/verify-otp');
            return;
        }

        $message = ($user !== null && $user['status'] === 'active')
            ? 'Email verified. You can now log in.'
            : 'Email verified. Please also verify your phone OTP to activate your account.';

        Session::flash('success', $message);
        $this->redirect('/login');
    }

    public function resendVerificationEmail(Request $request): void
    {
        $userId = Session::get('_pending_user_id');

        if ($userId === null) {
            $this->redirect('/register');
            return;
        }

        $user = User::find((int) $userId);

        if ($user !== null) {
            $rateLimitKey = 'email_verify_resend:' . $user['email'];

            if (RateLimiter::tooManyAttempts($rateLimitKey, 3, 10)) {
                Session::flash('error', 'Please wait a few minutes before requesting another verification email.');
                $this->redirect('/verify-otp');
                return;
            }

            RateLimiter::hit($rateLimitKey, $request->ip());
            $this->authService->resendVerificationEmail((int) $userId, $user['email'], $user['full_name']);
        }

        Session::flash('success', 'A new verification email has been sent.');
        $this->redirect('/verify-otp');
    }

    /**
     * Sets the appropriate flash message + clears the pending-registration
     * session once the account is fully active, based on which of the two
     * required verifications (phone/email) are still outstanding.
     */
    protected function flashVerificationProgress(int $userId): void
    {
        $user = User::find($userId);

        if ($user !== null && $user['status'] === 'active') {
            Session::forget('_pending_user_id');
            Session::flash('success', 'Account verified. You can now log in.');
            return;
        }

        $stillNeeded = ($user['phone_verified_at'] === null) ? 'phone OTP' : 'email';
        Session::flash('success', "Got it! Please also verify your {$stillNeeded} to activate your account.");
    }

    public function showLogin(Request $request): void
    {
        $this->view('auth/login', [], 'guest');
    }

    public function login(Request $request): void
    {
        $identifier = trim((string) $request->input('identifier', ''));
        $password = (string) $request->input('password', '');
        $remember = (bool) $request->input('remember', false);

        $result = $this->authService->attemptLogin($identifier, $password, $request);

        if (!$result['success']) {
            $message = match ($result['reason']) {
                'locked' => "Too many failed attempts. Try again in {$result['minutes']} minutes.",
                'inactive' => $this->inactiveMessage($result),
                default => 'Invalid email/phone or password.',
            };

            Session::flash('error', $message);
            $this->redirect('/login');
            return;
        }

        if ($remember) {
            (new RememberMeService())->issue((int) $result['user']['id']);
        }

        $this->redirect('/dashboard');
    }

    protected function inactiveMessage(array $result): string
    {
        if (!$result['phone_verified']) {
            return 'Please verify your phone OTP before logging in.';
        }

        if (!$result['email_verified']) {
            return 'Please verify your email before logging in - check your inbox for the verification link.';
        }

        return 'Your account is not active yet.';
    }

    public function logout(Request $request): void
    {
        $this->authService->logout();
        (new RememberMeService())->forget($request);
        $this->redirect('/login');
    }

    public function showForgotPassword(Request $request): void
    {
        $this->view('auth/forgot_password', [], 'guest');
    }

    public function sendResetLink(Request $request): void
    {
        $email = trim((string) $request->input('email', ''));

        // Checked before findByEmail(), regardless of whether the address is
        // registered, so the rate limit itself can't be used to tell a real
        // email apart from a fake one (same response either way).
        $rateLimitKey = 'password_reset:' . $email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3, 15)) {
            Session::flash('success', 'If that email exists, a reset link has been sent.');
            $this->redirect('/forgot-password');
            return;
        }

        RateLimiter::hit($rateLimitKey, $request->ip());

        $user = User::findByEmail($email);

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            PasswordReset::create($email, hash('sha256', $token), 30);

            $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($email));
            (new MailService())->sendPasswordReset($email, $user['full_name'], $resetUrl);
        }

        Session::flash('success', 'If that email exists, a reset link has been sent.');
        $this->redirect('/forgot-password');
    }

    public function showResetPassword(Request $request): void
    {
        $this->view('auth/reset_password', [
            'token' => $request->input('token', ''),
            'email' => $request->input('email', ''),
        ], 'guest');
    }

    public function resetPassword(Request $request): void
    {
        $email = trim((string) $request->input('email', ''));
        $token = (string) $request->input('token', '');
        $password = (string) $request->input('password', '');
        $confirmation = (string) $request->input('password_confirmation', '');

        if (strlen($password) < 8 || $password !== $confirmation) {
            Session::flash('error', 'Password must be at least 8 characters and match confirmation.');
            $this->redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            return;
        }

        $reset = PasswordReset::findValidByEmail($email);

        if ($reset === null || !hash_equals($reset['token_hash'], hash('sha256', $token))) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            $this->redirect('/forgot-password');
            return;
        }

        $user = User::findByEmail($email);

        if ($user !== null) {
            User::update((int) $user['id'], ['password_hash' => password_hash($password, PASSWORD_BCRYPT)]);
        }

        PasswordReset::markUsed((int) $reset['id']);

        Session::flash('success', 'Password reset successfully. Please log in.');
        $this->redirect('/login');
    }
}
