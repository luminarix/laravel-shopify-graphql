<?php

return [
    'api_version' => env('SHOPIFY_API_VERSION', '2025-01'),
    'fail_on_throttled' => env('SHOPIFY_FAIL_ON_THROTTLED', true),
    'throttle_max_tries' => env('SHOPIFY_THROTTLE_MAX_TRIES', 5),
];
