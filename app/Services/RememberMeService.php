<?php

namespace App\Services;

use App\Models\RememberToken;
use App\Models\User;
use Core\Request;

class RememberMeService
{
    public const COOKIE_NAME = 'road2job_remember';

    protected int $days;

    public function __construct()
    {
        $this->days = (int) config('auth.remember_me_days', 30);
    }

    public function issue(int $userId): void
    {
        $selector = bin2hex(random_bytes(16));
        $rawToken = bin2hex(random_bytes(32));

        RememberToken::create($userId, $selector, hash('sha256', $rawToken), $this->days);

        $this->setCookie($selector . ':' . $rawToken, time() + $this->days * 86400);
    }

    /**
     * Attempts to re-establish a session from the remember-me cookie.
     * Rotates the token on every successful use (mitigates cookie theft/replay).
     */
    public function attempt(Request $request): bool
    {
        $cookie = $request->cookie(self::COOKIE_NAME);

        if (!is_string($cookie) || !str_contains($cookie, ':')) {
            return false;
        }

        [$selector, $rawToken] = explode(':', $cookie, 2);

        $record = RememberToken::findValidBySelector($selector);

        if ($record === null || !hash_equals($record['token_hash'], hash('sha256', $rawToken))) {
            $this->clearCookie();
            return false;
        }

        RememberToken::deleteBySelector($selector);

        $user = User::findWithRole((int) $record['user_id']);

        if ($user === null || $user['status'] !== 'active') {
            $this->clearCookie();
            return false;
        }

        AuthService::establishSession($user, $request);
        $this->issue((int) $user['id']);

        return true;
    }

    public function forget(Request $request): void
    {
        $cookie = $request->cookie(self::COOKIE_NAME);

        if (is_string($cookie) && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            RememberToken::deleteBySelector($selector);
        }

        $this->clearCookie();
    }

    public function forgetAllForUser(int $userId): void
    {
        RememberToken::deleteAllForUser($userId);
    }

    protected function setCookie(string $value, int $expires): void
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        setcookie(self::COOKIE_NAME, $value, [
            'expires' => $expires,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    protected function clearCookie(): void
    {
        setcookie(self::COOKIE_NAME, '', ['expires' => time() - 3600, 'path' => '/']);
    }
}
