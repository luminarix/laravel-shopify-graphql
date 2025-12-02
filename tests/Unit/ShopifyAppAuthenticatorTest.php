<?php

declare(strict_types=1);

use Luminarix\Shopify\GraphQLClient\Authenticators\ShopifyApp;

it('creates authenticator with valid shop domain', function () {
    $auth = new ShopifyApp('test-shop.myshopify.com', 'token', '2025-01');

    expect($auth->getShopDomain())->toBe('test-shop.myshopify.com')
        ->and($auth->accessToken)->toBe('token')
        ->and($auth->getApiVersion())->toBe('2025-01');
});

it('strips https from shop domain', function () {
    $auth = new ShopifyApp('https://test-shop.myshopify.com', 'token', '2025-01');

    expect($auth->getShopDomain())->toBe('test-shop.myshopify.com');
});

it('strips http from shop domain', function () {
    $auth = new ShopifyApp('http://test-shop.myshopify.com', 'token', '2025-01');

    expect($auth->getShopDomain())->toBe('test-shop.myshopify.com');
});

it('strips trailing slash from shop domain', function () {
    $auth = new ShopifyApp('test-shop.myshopify.com/', 'token', '2025-01');

    expect($auth->getShopDomain())->toBe('test-shop.myshopify.com');
});

it('rejects invalid shop domain', function () {
    new ShopifyApp('invalid-domain.com', 'token', '2025-01');
})->throws(InvalidArgumentException::class, 'must end with ".myshopify.com"');

it('rejects invalid api version format', function () {
    new ShopifyApp('test-shop.myshopify.com', 'token', 'invalid');
})->throws(InvalidArgumentException::class, 'must match the pattern "YYYY-MM"');

it('uses config api version when not provided', function () {
    config(['shopify-graphql.api_version' => '2024-10']);

    $auth = new ShopifyApp('test-shop.myshopify.com', 'token');

    expect($auth->getApiVersion())->toBe('2024-10');
});
