<?php
return [
    'default_app' => env('APP_NAME', 'Job posting'),
    'dev_mode' => env('APP_ENV') !== 'production' ? 1: 0,
    'send_notification' => env('APP_ENV') !== 'production' ? 1: 0,
    'sms' => [
        'limit_count' => 3,
        'valid_in' => 10
    ],
    'logs' => [
        'debug' => 1,
        'info' => 1,
        'error' => 1
    ],
    'one_signal' => [
        'auth_key' => env('ONESIGNAL_USER_AUTH_KEY', 'auth_key'),
        'app_id' => env('ONESIGNAL_APP_ID', 'app_id'),
        'rest_api_key' => env('ONESIGNAL_REST_API_KEY', 'rest_api_key'),
    ],
];
