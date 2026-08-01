<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use Core\Request;
use Core\Session;

class AuthService
{
    protected OtpService $otpService;
    protected MailService $mailService;
    protected NotificationService $notificationService;
    protected EmailVerificationService $emailVerificationService;

    public function __construct(
        ?OtpService $otpService = null,
        ?MailService $mailService = null,
        ?NotificationService $notificationService = null,
        ?EmailVerificationService $emailVerificationService = null
    ) {
        $this->otpService = $otpService ?? new OtpService();
        $this->mailService = $mailService ?? new MailService();
        $this->notificationService = $notificationService ?? new NotificationService();
        $this->emailVerificationService = $emailVerificationService ?? new EmailVerificationService();
    }

    public function register(array $data): array
    {
        $role = Role::findBySlug($data['role']);

        if ($role === null || !$role['self_registerable']) {
            throw new \InvalidArgumentException('Invalid role selected.');
        }

        $requireOtp = (bool) config('auth.require_otp', true);
        $now = date('Y-m-d H:i:s');

        $insertData = [
            'role_id' => $role['id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'status' => $requireOtp ? 'pending' : 'active',
        ];

        if (!$requireOtp) {
            $insertData['phone_verified_at'] = $now;
            $insertData['email_verified_at'] = $now;
        }

        if ($role['slug'] === 'student') {
            $insertData['username'] = User::generateUniqueUsername($data['full_name']);
        }

        $userId = (int) User::insert($insertData);

        if ($requireOtp) {
            $this->otpService->generateAndSend($userId, $data['phone'], 'registration');
            $this->emailVerificationService->generateAndSend($userId, $data['email'], $data['full_name']);
            AuditLog::record($userId, 'register', 'Account created, pending OTP + email verification.', $data['ip'] ?? '');
        } else {
            AuditLog::record($userId, 'register', 'Account created and auto-activated (OTP verification disabled via REQUIRE_OTP=false).', $data['ip'] ?? '');
        }

        return ['user_id' => $userId, 'role_slug' => $role['slug'], 'auto_activated' => !$requireOtp];
    }

    public function verifyOtp(int $userId, string $code): bool
    {
        if (!$this->otpService->verify($userId, 'registration', $code)) {
            return false;
        }

        User::update($userId, ['phone_verified_at' => date('Y-m-d H:i:s')]);
        AuditLog::record($userId, 'otp_verified', 'Phone number verified.');

        $this->maybeActivate($userId);

        return true;
    }

    public function verifyEmail(string $email, string $token): bool
    {
        if (!$this->emailVerificationService->verify($email, $token)) {
            return false;
        }

        $user = User::findByEmail($email);
        User::update((int) $user['id'], ['email_verified_at' => date('Y-m-d H:i:s')]);
        AuditLog::record((int) $user['id'], 'email_verified', 'Email address verified.');

        $this->maybeActivate((int) $user['id']);

        return true;
    }

    /**
     * Activates the account only once both phone and email are verified.
     */
    protected function maybeActivate(int $userId): void
    {
        $user = User::find($userId);

        if ($user === null || $user['status'] === 'active') {
            return;
        }

        if ($user['phone_verified_at'] === null || $user['email_verified_at'] === null) {
            return;
        }

        User::activate($userId);

        $this->mailService->sendWelcome($user['email'], $user['full_name']);
        $this->notificationService->notify($userId, 'Welcome to Road2Job', 'Your account is now active.');

        AuditLog::record($userId, 'account_activated', 'Both phone and email verified - account activated.');
    }

    public function resendOtp(int $userId, string $phone): void
    {
        $this->otpService->generateAndSend($userId, $phone, 'registration');
    }

    public function resendVerificationEmail(int $userId, string $email, string $fullName): void
    {
        $this->emailVerificationService->generateAndSend($userId, $email, $fullName);
    }

    public function attemptLogin(string $identifier, string $password, Request $request): array
    {
        $limiter = new LoginAttemptLimiter();

        if ($limiter->tooManyAttempts($identifier, $request->ip())) {
            return ['success' => false, 'reason' => 'locked', 'minutes' => $limiter->minutesUntilUnlock()];
        }

        $user = User::findByIdentifier($identifier);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $limiter->recordFailure($identifier, $request->ip());
            return ['success' => false, 'reason' => 'invalid'];
        }

        if ($user['status'] !== 'active') {
            $limiter->recordFailure($identifier, $request->ip());

            return [
                'success' => false,
                'reason' => 'inactive',
                'phone_verified' => $user['phone_verified_at'] !== null,
                'email_verified' => $user['email_verified_at'] !== null,
            ];
        }

        $limiter->recordSuccess($identifier, $request->ip());

        $userWithRole = User::findWithRole((int) $user['id']);
        self::establishSession($userWithRole, $request);

        AuditLog::record((int) $user['id'], 'login', 'Successful login.', $request->ip());

        return ['success' => true, 'user' => $userWithRole];
    }

    /**
     * Shared session bootstrap used by both password login and remember-me
     * auto-login (RememberMeService calls this statically to avoid a
     * circular service dependency).
     */
    public static function establishSession(array $userWithRole, Request $request): void
    {
        Session::regenerate();
        Session::put('_user', [
            'id' => (int) $userWithRole['id'],
            'full_name' => $userWithRole['full_name'],
            'email' => $userWithRole['email'],
            'role_id' => (int) $userWithRole['role_id'],
            'role_slug' => $userWithRole['role_slug'],
            'role_label' => $userWithRole['role_label'],
        ]);

        User::touchLastLogin((int) $userWithRole['id']);
        $sessionId = UserSession::open((int) $userWithRole['id'], $request->ip(), $request->userAgent(), session_id());
        Session::put('_user_session_id', (int) $sessionId);
    }

    public function logout(): void
    {
        $sessionId = Session::get('_user_session_id');

        if ($sessionId !== null) {
            UserSession::close((int) $sessionId);
        }

        $user = Session::get('_user');
        if ($user !== null) {
            AuditLog::record($user['id'], 'logout', 'User logged out.');
        }

        Session::destroy();
    }
}
