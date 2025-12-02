<?php

declare(strict_types=1);

use Luminarix\Shopify\GraphQLClient\Data\RateLimitState;

it('creates instance with default values', function () {
    $state = new RateLimitState;

    expect($state->requestedQueryCost)->toBeNull()
        ->and($state->actualQueryCost)->toBeNull()
        ->and($state->maximumAvailable)->toBeNull()
        ->and($state->currentlyAvailable)->toBeNull()
        ->and($state->restoreRate)->toBeNull()
        ->and($state->isThrottled)->toBeFalse();
});

it('creates instance from response', function () {
    $response = [
        'extensions' => [
            'cost' => [
                'requestedQueryCost' => 10,
                'actualQueryCost' => 5,
                'throttleStatus' => [
                    'maximumAvailable' => 1_000,
                    'currentlyAvailable' => 995,
                    'restoreRate' => 50,
                ],
            ],
        ],
    ];

    $state = RateLimitState::fromResponse($response);

    expect($state->requestedQueryCost)->toBe(10)
        ->and($state->actualQueryCost)->toBe(5)
        ->and($state->maximumAvailable)->toBe(1_000)
        ->and($state->currentlyAvailable)->toBe(995)
        ->and($state->restoreRate)->toBe(50)
        ->and($state->isThrottled)->toBeFalse();
});

it('creates throttled state from response', function () {
    $response = [
        'extensions' => [
            'cost' => [
                'requestedQueryCost' => 100,
                'actualQueryCost' => null,
            ],
        ],
    ];

    $state = RateLimitState::fromResponse($response, isThrottled: true);

    expect($state->isThrottled)->toBeTrue();
});

it('converts to array', function () {
    $state = new RateLimitState(
        requestedQueryCost: 10,
        actualQueryCost: 5,
        maximumAvailable: 1_000,
        currentlyAvailable: 995,
        restoreRate: 50,
        isThrottled: false,
    );

    expect($state->toArray())->toBe([
        'requestedQueryCost' => 10,
        'actualQueryCost' => 5,
        'maxAvailableLimit' => 1_000,
        'lastAvailableLimit' => 995,
        'restoreRate' => 50,
        'isThrottled' => false,
    ]);
});

it('converts to service array', function () {
    $state = new RateLimitState(
        maximumAvailable: 1_000,
        currentlyAvailable: 995,
        restoreRate: 50,
    );

    expect($state->toServiceArray())->toBe([
        'maximumAvailable' => 1_000,
        'currentlyAvailable' => 995,
        'restoreRate' => 50,
    ]);
});
