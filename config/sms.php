<?php

return [
    'fast2sms' => [
        'api_key' => env('FAST2SMS_API_KEY', ''),
        'sender_id' => env('FAST2SMS_SENDER_ID', 'FSTSMS'),
        'route' => env('FAST2SMS_ROUTE', 'otp'),
        'endpoint' => 'https://www.fast2sms.com/dev/bulkV2',
        'country_code' => env('FAST2SMS_COUNTRY_CODE', '91'),
    ],
    'whatsapp' => [
        // Requires a Meta-approved AUTHENTICATION/OTP-purpose template in the
        // Fast2SMS WABA template manager. Leave template_name blank until one
        // is approved - OtpService will log and skip sending until then.
        'api_version' => env('FAST2SMS_WABA_API_VERSION', 'v24.0'),
        'phone_number_id' => env('FAST2SMS_WABA_PHONE_NUMBER_ID', ''),
        'template_name' => env('FAST2SMS_WABA_TEMPLATE_NAME', ''),
        'template_language' => env('FAST2SMS_WABA_TEMPLATE_LANGUAGE', 'en'),
    ],
    // 'sms' or 'whatsapp'
    'otp_channel' => env('OTP_CHANNEL', 'sms'),
    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
    ],
];
