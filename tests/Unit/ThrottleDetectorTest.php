<?php

declare(strict_types=1);

use Luminarix\Shopify\GraphQLClient\Services\ThrottleDetector;

it('detects throttled response', function () {
    $detector = new ThrottleDetector;

    $response = [
        'errors' => [
            [
                'message' => 'Throttled',
                'extensions' => [
                    'code' => 'THROTTLED',
                ],
            ],
        ],
    ];

    expect($detector->isThrottled($response))->toBeTrue();
});

it('detects non-throttled response', function () {
    $detector = new ThrottleDetector;

    $response = [
        'data' => [
            'shop' => ['name' => 'Test Shop'],
        ],
    ];

    expect($detector->isThrottled($response))->toBeFalse();
});

it('detects non-throttled error response', function () {
    $detector = new ThrottleDetector;

    $response = [
        'errors' => [
            [
                'message' => 'Some other error',
                'extensions' => [
                    'code' => 'INVALID_QUERY',
                ],
            ],
        ],
    ];

    expect($detector->isThrottled($response))->toBeFalse();
});

it('returns throttle code', function () {
    $detector = new ThrottleDetector;

    expect($detector->getThrottleCode())->toBe('THROTTLED');
});
