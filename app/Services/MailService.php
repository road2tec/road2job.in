<?php

namespace App\Services;

use Core\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailService
{
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $bodyHtml,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        $config = config('mail');

        if (empty($config['host']) || empty($config['username'])) {
            Logger::warning('SMTP not configured - email not sent, logged instead.', ['to' => $toEmail, 'subject' => $subject]);
            return config('app.debug') === true;
        }

        if (!class_exists(PHPMailer::class)) {
            Logger::warning('PHPMailer not installed (run composer install) - email not sent, logged instead.', ['to' => $toEmail, 'subject' => $subject]);
            return config('app.debug') === true;
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = $config['host'];
            $mailer->Port = $config['port'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $config['username'];
            $mailer->Password = $config['password'];
            $mailer->SMTPSecure = $config['encryption'];
            $mailer->CharSet = 'UTF-8';

            $mailer->setFrom($config['from_address'], $config['from_name']);
            $mailer->addAddress($toEmail, $toName);

            if ($replyToEmail !== null) {
                $mailer->addReplyTo($replyToEmail, $replyToName ?? '');
            }

            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $bodyHtml;

            $mailer->send();

            return true;
        } catch (PHPMailerException $e) {
            Logger::error('Mail send failed: ' . $mailer->ErrorInfo, ['to' => $toEmail]);
            return false;
        }
    }

    public function sendWelcome(string $toEmail, string $toName): bool
    {
        $subject = 'Welcome to Road2Job';
        $body = '<p>Hi ' . e($toName) . ',</p><p>Your Road2Job account has been created successfully. Start building your career profile today.</p>';

        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        $subject = 'Reset your Road2Job password';
        $body = '<p>Hi ' . e($toName) . ',</p><p>Click the link below to reset your password. This link expires in 30 minutes.</p><p><a href="' . e($resetUrl) . '">' . e($resetUrl) . '</a></p>';

        return $this->send($toEmail, $toName, $subject, $body);
    }
}
