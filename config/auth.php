<?php

return [
    'remember_me_days' => (int) env('REMEMBER_ME_DAYS', 30),
    'email_verification_expiry_minutes' => (int) env('EMAIL_VERIFICATION_EXPIRY_MINUTES', 60),
    // When false, registrations skip OTP + email verification entirely and
    // activate immediately. Must stay true for any real deployment - only
    // meant to be flipped off for local demo/testing convenience.
    'require_otp' => filter_var(env('REQUIRE_OTP', 'true'), FILTER_VALIDATE_BOOLEAN),
];
