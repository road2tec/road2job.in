<?php

return [
    'name' => env('APP_NAME', 'Road2Job'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim(env('APP_URL', 'http://localhost'), '/'),
    'key' => env('APP_KEY', ''),
    'timezone' => 'Asia/Kolkata',
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
];
