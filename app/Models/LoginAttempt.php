<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class LoginAttempt extends Model
{
    protected static string $table = 'login_attempts';
    protected static bool $timestamps = false;

    public static function record(string $identifier, string $ip, bool $success): string
    {
        return static::insert([
            'identifier' => $identifier,
            'ip_address' => $ip,
            'success' => $success ? 1 : 0,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function recentFailures(string $identifier, string $ip, int $minutes): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE (identifier = :identifier OR ip_address = :ip)
               AND success = 0
               AND attempted_at >= :since"
        );
        $stmt->execute([
            'identifier' => $identifier,
            'ip' => $ip,
            'since' => date('Y-m-d H:i:s', time() - $minutes * 60),
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Like recentFailures(), but counts every record regardless of the
     * success flag - used for rate-limiting endpoints that aren't a
     * pass/fail login attempt (OTP/verification-email resends, password
     * reset requests), where every call should count against the window.
     *
     * Deliberately matches by identifier only, NOT "OR ip_address" the way
     * recentFailures() does for login lockout - RateLimiter callers pass a
     * namespaced identifier per action (e.g. "otp_resend:{phone}"), and
     * OR-ing in the IP would let one rate-limited action's hits count
     * against a completely different action's limit just because they
     * came from the same IP (e.g. resend-OTP hits tripping the unrelated
     * resend-verification-email limit for the same visitor).
     */
    public static function recentCount(string $identifier, int $minutes): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE identifier = :identifier
               AND attempted_at >= :since"
        );
        $stmt->execute([
            'identifier' => $identifier,
            'since' => date('Y-m-d H:i:s', time() - $minutes * 60),
        ]);

        return (int) $stmt->fetchColumn();
    }

    public static function historyForIdentifiers(array $identifiers, int $limit = 20): array
    {
        $identifiers = array_values(array_unique(array_filter($identifiers, fn ($v) => $v !== null && $v !== '')));

        if (empty($identifiers)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($identifiers), '?'));

        $stmt = Database::connection()->prepare(
            "SELECT * FROM login_attempts WHERE identifier IN ({$placeholders}) ORDER BY attempted_at DESC LIMIT " . (int) $limit
        );
        $stmt->execute($identifiers);

        return $stmt->fetchAll();
    }
}
