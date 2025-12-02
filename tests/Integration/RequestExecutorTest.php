<?php

declare(strict_types=1);

use Luminarix\Shopify\GraphQLClient\Authenticators\ShopifyApp;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientRequestFailedException;
use Luminarix\Shopify\GraphQLClient\Integrations\Requests\Query;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;
use Luminarix\Shopify\GraphQLClient\Services\NullRateLimitService;
use Luminarix\Shopify\GraphQLClient\Services\RequestExecutor;
use Luminarix\Shopify\GraphQLClient\Services\ThrottleDetector;
use Luminarix\Shopify\GraphQLClient\Tests\Fixtures\MockResponses;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    MockClient::destroyGlobal();
    $authenticator = new ShopifyApp('test-shop.myshopify.com', 'test-token', '2025-01');
    $this->connector = new ShopifyConnector($authenticator);
});

afterEach(function () {
    MockClient::destroyGlobal();
});

it('executes a request and returns response data', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::shopQuery()),
    ]);

    $executor = new RequestExecutor(
        $this->connector,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $result = $executor->execute(new Query('{ shop { name } }'));

    expect($result['data']['shop']['name'])->toBe('Test Shop');
});

it('tracks rate limit state after execution', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::shopQuery()),
    ]);

    $executor = new RequestExecutor(
        $this->connector,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $executor->execute(new Query('{ shop { name } }'));
    $state = $executor->getLastRateLimitState();

    expect($state)->not->toBeNull()
        ->and($state->requestedQueryCost)->toBe(2)
        ->and($state->actualQueryCost)->toBe(2)
        ->and($state->isThrottled)->toBeFalse();
});

it('detects throttled response', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::throttledResponse()),
    ]);

    config(['shopify-graphql.fail_on_throttled' => false]);
    config(['shopify-graphql.throttle_max_tries' => 0]);

    $executor = new RequestExecutor(
        $this->connector,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $executor->execute(new Query('{ shop { name } }'));
    $state = $executor->getLastRateLimitState();

    expect($state->isThrottled)->toBeTrue();
});

it('throws exception on http error', function () {
    MockClient::global([
        '*' => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $executor = new RequestExecutor(
        $this->connector,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $executor->execute(new Query('{ shop { name } }'));
})->throws(ClientRequestFailedException::class);

it('throws exception on graphql error', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::graphqlError()),
    ]);

    $executor = new RequestExecutor(
        $this->connector,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $executor->execute(new Query('{ shop { invalid } }'));
})->throws(ClientRequestFailedException::class);

it('skips rate limit tracking when disabled', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::bulkOperationCompleted()),
    ]);

    $executor = new RequestExecutor(
        $this->connector,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $executor->execute(new Query('{ node(id: "gid://shopify/BulkOperation/123") { id } }'), trackRateLimits: false);
    $state = $executor->getLastRateLimitState();

    expect($state)->toBeNull();
});

it('returns null rate limit state before any request', function () {
    $executor = new RequestExecutor(
        $this->connector,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    expect($executor->getLastRateLimitState())->toBeNull();
});
