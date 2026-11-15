<?php

return [
    'url' => env('WAHA_URL'),
    'api_key' => env('WAHA_API_KEY'),
    'session' => env('WAHA_SESSION', 'default'),
    'timeout' => (int) env('WAHA_TIMEOUT', 10),
];
