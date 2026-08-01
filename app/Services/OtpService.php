<?php

namespace App\Services;

use App\Models\OtpVerification;
use Core\Logger;

class OtpService
{
    protected int $length;
    protected int $expiryMinutes;

    public function __construct()
    {
        $this->length = (int) config('sms.otp.length', 6);
        $this->expiryMinutes = (int) config('sms.otp.expiry_minutes', 10);
    }

    public function generateAndSend(int $userId, string $phone, string $purpose = 'registration'): bool
    {
        $otp = $this->generateCode();

        OtpVerification::create($userId, $phone, $otp, $purpose, $this->expiryMinutes);

        return $this->dispatch($phone, $otp);
    }

    public function verify(int $userId, string $purpose, string $code): bool
    {
        $record = OtpVerification::latestFor($userId, $purpose);

        if ($record === null) {
            return false;
        }

        if (strtotime($record['expires_at']) < time()) {
            return false;
        }

        if ((int) $record['attempts'] >= 5) {
            return false;
        }

        OtpVerification::incrementAttempts((int) $record['id']);

        if (!hash_equals($record['otp_code'], $code)) {
            return false;
        }

        OtpVerification::markVerified((int) $record['id']);

        return true;
    }

    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 10 ** $this->length - 1), $this->length, '0', STR_PAD_LEFT);
    }

    protected function dispatch(string $phone, string $otp): bool
    {
        $channel = config('sms.otp_channel', 'sms');

        return $channel === 'whatsapp'
            ? $this->dispatchWhatsapp($phone, $otp)
            : $this->dispatchSms($phone, $otp);
    }

    protected function dispatchSms(string $phone, string $otp): bool
    {
        $config = config('sms.fast2sms');

        if (empty($config['api_key'])) {
            Logger::warning('Fast2SMS API key missing - OTP not sent, logged instead.', ['phone' => $phone, 'otp' => $otp]);
            return config('app.debug') === true;
        }

        $payload = [
            'route' => $config['route'],
            'variables_values' => $otp,
            'numbers' => $phone,
        ];

        [$response, $error] = $this->postFast2Sms(
            $config['endpoint'] . '?' . http_build_query($payload),
            $config['api_key']
        );

        if ($error) {
            Logger::error('Fast2SMS SMS request failed: ' . $error, ['phone' => $phone]);
            return false;
        }

        $result = json_decode((string) $response, true);
        $success = is_array($result) && ($result['return'] ?? false) === true;

        if (!$success) {
            Logger::error('Fast2SMS SMS API returned failure.', ['phone' => $phone, 'response' => $response]);
        }

        return $success;
    }

    protected function dispatchWhatsapp(string $phone, string $otp): bool
    {
        $waConfig = config('sms.whatsapp');
        $apiKey = config('sms.fast2sms.api_key');

        if (empty($waConfig['template_name']) || empty($waConfig['phone_number_id']) || empty($apiKey)) {
            Logger::warning(
                'Fast2SMS WhatsApp OTP not configured (missing template_name/phone_number_id/api_key) - OTP not sent, logged instead.',
                ['phone' => $phone, 'otp' => $otp]
            );
            return config('app.debug') === true;
        }

        $countryCode = config('sms.fast2sms.country_code', '91');
        $to = str_starts_with($phone, $countryCode) ? $phone : $countryCode . $phone;

        $url = sprintf(
            'https://www.fast2sms.com/dev/whatsapp/%s/%s/messages',
            $waConfig['api_version'],
            $waConfig['phone_number_id']
        );

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $waConfig['template_name'],
                'language' => ['code' => $waConfig['template_language']],
                'components' => [
                    ['type' => 'body', 'parameters' => [['type' => 'text', 'text' => $otp]]],
                ],
            ],
        ];

        [$response, $error] = $this->postFast2Sms($url, $apiKey, json_encode($body), true);

        if ($error) {
            Logger::error('Fast2SMS WhatsApp request failed: ' . $error, ['phone' => $phone]);
            return false;
        }

        $result = json_decode((string) $response, true);
        $success = is_array($result) && ($result['success'] ?? false) === true;

        if (!$success) {
            Logger::error('Fast2SMS WhatsApp API returned failure.', ['phone' => $phone, 'response' => $response]);
        }

        return $success;
    }

    /**
     * @return array{0: string|false, 1: string}
     */
    protected function postFast2Sms(string $url, string $apiKey, ?string $jsonBody = null, bool $isJson = false): array
    {
        $headers = [
            'authorization: ' . $apiKey,
            'cache-control: no-cache',
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ];

        if ($isJson) {
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $jsonBody;
        }

        $options[CURLOPT_HTTPHEADER] = $headers;

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return [$response, $error];
    }
}
