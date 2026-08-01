<?php

namespace App\Services;

use App\Models\LoginAttempt;

class LoginAttemptLimiter
{
    protected int $maxAttempts;
    protected int $lockoutMinutes;

    public function __construct()
    {
        $this->maxAttempts = (int) env('LOGIN_MAX_ATTEMPTS', 5);
        $this->lockoutMinutes = (int) env('LOGIN_LOCKOUT_MINUTES', 15);
    }

    public function tooManyAttempts(string $identifier, string $ip): bool
    {
        return LoginAttempt::recentFailures($identifier, $ip, $this->lockoutMinutes) >= $this->maxAttempts;
    }

    public function recordFailure(string $identifier, string $ip): void
    {
        LoginAttempt::record($identifier, $ip, false);
    }

    public function recordSuccess(string $identifier, string $ip): void
    {
        LoginAttempt::record($identifier, $ip, true);
    }

    public function minutesUntilUnlock(): int
    {
        return $this->lockoutMinutes;
    }
}
