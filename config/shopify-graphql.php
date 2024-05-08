<?php

return [
    'api_version' => env('SHOPIFY_API_VERSION', '2024-04'),
    'client_id' => env('SHOPIFY_CLIENT_ID'),
    'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
    'scopes' => [
        'read_products',
    ],
];
