<?php

namespace App\Services;

use App\Models\LoginAttempt;

/**
 * Generic windowed rate limiter, backed by the same login_attempts table
 * LoginAttemptLimiter uses for login lockout - callers pass a namespaced
 * key (e.g. "otp_resend:{$phone}") to keep their events distinct from
 * real login attempts and from each other. Unlike LoginAttemptLimiter
 * (env-configured, login-specific, IP-inclusive), this is parameterized
 * per call so different endpoints can use different max-attempts/window
 * combinations, and matches by key alone - it deliberately does not fall
 * back to IP-based matching, so unrelated rate-limited actions from the
 * same visitor never trip each other's limits.
 */
class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $windowMinutes): bool
    {
        return LoginAttempt::recentCount($key, $windowMinutes) >= $maxAttempts;
    }

    public static function hit(string $key, string $ip): void
    {
        // Recorded as success=true so LoginAttemptLimiter's recentFailures()
        // (which only counts success=0 rows) never sees these events.
        LoginAttempt::record($key, $ip, true);
    }
}
