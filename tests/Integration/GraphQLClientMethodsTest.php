<?php

declare(strict_types=1);

use Luminarix\Shopify\GraphQLClient\Authenticators\ShopifyApp;
use Luminarix\Shopify\GraphQLClient\GraphQLClientMethods;
use Luminarix\Shopify\GraphQLClient\Services\NullRateLimitService;
use Luminarix\Shopify\GraphQLClient\Services\ThrottleDetector;
use Luminarix\Shopify\GraphQLClient\Tests\Fixtures\MockResponses;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->authenticator = new ShopifyApp('test-shop.myshopify.com', 'test-token', '2025-01');
    MockClient::destroyGlobal();
});

afterEach(function () {
    MockClient::destroyGlobal();
});

it('executes a query and returns data', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::shopQuery()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $result = $client->query('{ shop { name email } }');

    expect($result->toArray())
        ->toBe(['shop' => ['name' => 'Test Shop', 'email' => 'test@example.com']]);
});

it('executes a query with extensions', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::shopQuery()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $result = $client->query('{ shop { name } }', withExtensions: true);
    $data = $result->toArray();

    expect($data)->toHaveKey('data')
        ->and($data)->toHaveKey('extensions')
        ->and($data['extensions']['cost']['requestedQueryCost'])->toBe(2);
});

it('executes a mutation with variables', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::productCreateMutation()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $mutation = 'mutation ($input: ProductInput!) { productCreate(input: $input) { product { id title } userErrors { field message } } }';

    $result = $client->mutate($mutation, ['input' => ['title' => 'New Product']]);

    expect($result->toArray()['productCreate']['product'])
        ->toBe(['id' => 'gid://shopify/Product/123', 'title' => 'New Product']);
});

it('returns rate limit info after request', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::shopQuery()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $client->query('{ shop { name } }');
    $rateLimitInfo = $client->getRateLimitInfo();

    expect($rateLimitInfo['requestedQueryCost'])->toBe(2)
        ->and($rateLimitInfo['actualQueryCost'])->toBe(2)
        ->and($rateLimitInfo['maxAvailableLimit'])->toBe(2_000)
        ->and($rateLimitInfo['lastAvailableLimit'])->toBe(1_998)
        ->and($rateLimitInfo['restoreRate'])->toBe(100)
        ->and($rateLimitInfo['isThrottled'])->toBeFalse();
});

it('returns empty rate limit info before any request', function () {
    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $rateLimitInfo = $client->getRateLimitInfo();

    expect($rateLimitInfo['requestedQueryCost'])->toBeNull()
        ->and($rateLimitInfo['isThrottled'])->toBeFalse();
});

it('creates a bulk operation', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::bulkOperationCreated()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $result = $client->createBulkOperation('{ products { edges { node { id } } } }');

    expect($result->toArray()['bulkOperation'])
        ->toBe(['id' => 'gid://shopify/BulkOperation/123456', 'status' => 'CREATED']);
});

it('gets a bulk operation by id', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::bulkOperationCompleted()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $result = $client->getBulkOperation(123_456);

    expect($result->toArray())
        ->toHaveKey('id', 'gid://shopify/BulkOperation/123456')
        ->and($result->toArray()['status'])->toBe('COMPLETED');
});

it('gets current bulk operation', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::currentBulkOperationRunning()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $result = $client->getCurrentBulkOperation();

    expect($result->toArray())
        ->toBe(['id' => 'gid://shopify/BulkOperation/123456', 'status' => 'RUNNING']);
});

it('converts result to collection', function () {
    MockClient::global([
        '*' => MockResponse::make(MockResponses::productsQuery()),
    ]);

    $client = new GraphQLClientMethods(
        $this->authenticator,
        new NullRateLimitService,
        new ThrottleDetector,
    );

    $result = $client->query('{ products(first: 10) { edges { node { id title } } } }');
    $collection = $result->toCollection();

    expect($collection)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($collection->get('products')['edges'])->toHaveCount(1);
});
