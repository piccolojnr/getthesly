<?php

return [
    'url' => env('ANALYSIS_SERVICE_URL', 'http://127.0.0.1:8100'),
    'token' => env('ANALYSIS_SERVICE_TOKEN'),
    'connect_timeout' => (int) env('ANALYSIS_SERVICE_CONNECT_TIMEOUT', 2),
    'timeout' => (int) env('ANALYSIS_SERVICE_TIMEOUT', 8),
];
