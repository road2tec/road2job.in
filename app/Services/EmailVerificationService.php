<?php

namespace App\Services;

use App\Models\EmailVerification;
use App\Models\User;
use Core\Logger;

class EmailVerificationService
{
    protected int $expiryMinutes;

    public function __construct()
    {
        $this->expiryMinutes = (int) config('auth.email_verification_expiry_minutes', 60);
    }

    public function generateAndSend(int $userId, string $email, string $fullName): bool
    {
        $token = bin2hex(random_bytes(32));

        EmailVerification::create($userId, $email, hash('sha256', $token), $this->expiryMinutes);

        $verifyUrl = url('/verify-email?token=' . $token . '&email=' . urlencode($email));

        // This is the only place the raw token exists (it's hashed at rest),
        // so log it regardless of send outcome - needed to test locally
        // while SMTP is still a placeholder (see MailService::send).
        Logger::info('Email verification link generated.', ['email' => $email, 'url' => $verifyUrl]);

        $body = '<p>Hi ' . e($fullName) . ',</p><p>Please verify your email address to activate your Road2Job account.</p>'
            . '<p><a href="' . e($verifyUrl) . '">' . e($verifyUrl) . '</a></p>'
            . "<p>This link expires in {$this->expiryMinutes} minutes.</p>";

        return (new MailService())->send($email, $fullName, 'Verify your Road2Job email', $body);
    }

    public function verify(string $email, string $token): bool
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            return false;
        }

        $record = EmailVerification::latestFor((int) $user['id']);

        if ($record === null || $record['email'] !== $email) {
            return false;
        }

        if (strtotime($record['expires_at']) < time()) {
            return false;
        }

        if (!hash_equals($record['token_hash'], hash('sha256', $token))) {
            return false;
        }

        EmailVerification::markVerified((int) $record['id']);

        return true;
    }
}
