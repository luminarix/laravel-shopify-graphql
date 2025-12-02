# GraphQL client for Shopify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/luminarix/laravel-shopify-graphql.svg?style=flat-square)](https://packagist.org/packages/luminarix/laravel-shopify-graphql)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/luminarix/laravel-shopify-graphql/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/luminarix/laravel-shopify-graphql/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/luminarix/laravel-shopify-graphql/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/luminarix/laravel-shopify-graphql/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/luminarix/laravel-shopify-graphql.svg?style=flat-square)](https://packagist.org/packages/luminarix/laravel-shopify-graphql)

A Laravel package for interacting with Shopify's GraphQL Admin API. Built on [Saloon](https://github.com/saloonphp/saloon) with automatic rate limiting and retry handling.

## Installation

```bash
composer require luminarix/laravel-shopify-graphql
```

Publish the config file:

```bash
php artisan vendor:publish --tag="shopify-graphql-config"
```

## Basic Usage

```php
use Luminarix\Shopify\GraphQLClient\Facades\GraphQLClient;
use Luminarix\Shopify\GraphQLClient\Authenticators\ShopifyApp;

$authenticator = new ShopifyApp($shopDomain, $accessToken);
$client = GraphQLClient::factory()->create($authenticator);

// Query
$response = $client->query('
    query {
        shop {
            name
            email
        }
    }
');

$data = $response->toArray();

// Mutation
$response = $client->mutate('
    mutation orderMarkAsPaid($input: OrderMarkAsPaidInput!) {
        orderMarkAsPaid(input: $input) {
            order {
                id
            }
            userErrors {
                field
                message
            }
        }
    }
', [
    'input' => [
        'id' => 'gid://shopify/Order/123456789',
    ],
]);
```

## Eloquent Model Integration

Create a service class to manage client instances:

```php
namespace App\Services;

use App\Models\ShopifyShop;
use Luminarix\Shopify\GraphQLClient\Facades\GraphQLClient;
use Luminarix\Shopify\GraphQLClient\Authenticators\ShopifyApp;
use Luminarix\Shopify\GraphQLClient\GraphQLClientMethods;

class ShopifyGraphQLService
{
    public function with(ShopifyShop $shop): GraphQLClientMethods
    {
        $authenticator = new ShopifyApp($shop->domain, $shop->access_token);

        return GraphQLClient::factory()->create($authenticator);
    }
}
```

Create a trait for your models:

```php
namespace App\Traits;

use App\Services\ShopifyGraphQLService;
use Luminarix\Shopify\GraphQLClient\GraphQLClientMethods;

trait InteractsWithShopifyGraphQL
{
    public function graphql(): GraphQLClientMethods
    {
        return app(ShopifyGraphQLService::class)->with($this);
    }
}
```

Use on your Shopify shop model:

```php
namespace App\Models;

use App\Traits\InteractsWithShopifyGraphQL;
use Illuminate\Database\Eloquent\Model;

class ShopifyShop extends Model
{
    use InteractsWithShopifyGraphQL;
}
```

Now you can call GraphQL directly from your model:

```php
$shop = ShopifyShop::find(1);

$result = $shop->graphql()->query('
    query {
        shop {
            name
        }
    }
')->toArray();
```

## Response Handling

All query and mutation methods return a `GraphQLClientTransformer`:

```php
$response = $client->query($query);

$response->toArray();       // array
$response->toCollection();  // Illuminate\Support\Collection
$response->toFluent();      // Illuminate\Support\Fluent
$response->toJson();        // string
$response->toDTO(MyDTO::class);  // Custom DTO instance
```

The transformer is also equipped with the `Macroable` trait so you can add your own custom methods if needed.

### Getting Response with Extensions

Pass `withExtensions: true` to include cost and rate limit data:

```php
$response = $client->query($query, withExtensions: true);
// Returns: ['data' => [...], 'extensions' => ['cost' => [...]]]
```

## Bulk Operations

For large data exports, use bulk operations:

```php
// Start a bulk operation
$result = $client->createBulkOperation('
    {
        products {
            edges {
                node {
                    id
                    title
                }
            }
        }
    }
');

// Check current bulk operation status
$status = $client->getCurrentBulkOperation();

// Get a specific bulk operation by ID
$operation = $client->getBulkOperation($numericId);

// Cancel running bulk operation
$client->cancelBulkOperation();
```

## Rate Limiting

The package automatically handles Shopify's rate limits:

- Tracks query costs from response extensions
- Waits when throttled before retrying
- Configurable retry attempts

Get current rate limit info:

```php
$info = $client->getRateLimitInfo();
// [
//     'requestedQueryCost' => 10,
//     'actualQueryCost' => 8,
//     'maxAvailableLimit' => 1000,
//     'lastAvailableLimit' => 992,
//     'restoreRate' => 50,
//     'isThrottled' => false,
// ]
```

### Custom Rate Limit Service

Implement `RateLimitable` to customize rate limit handling:

```php
use Luminarix\Shopify\GraphQLClient\Contracts\RateLimitable;

class CustomRateLimitService implements RateLimitable
{
    public function getRateLimitInfo(): array { /* ... */ }
    public function updateRateLimitInfo(array $data): void { /* ... */ }
    public function calculateWaitTime(float $requestedQueryCost): float { /* ... */ }
    public function waitIfNecessary(float $requestedQueryCost): void { /* ... */ }
}

$client = GraphQLClient::factory()->create($authenticator, new CustomRateLimitService());
```

## Configuration

```php
// config/shopify-graphql.php
return [
    // Shopify API version (format: YYYY-MM)
    'api_version' => env('SHOPIFY_API_VERSION', '2025-01'),

    // Fail immediately when throttled (vs. waiting and retrying)
    'fail_on_throttled' => env('SHOPIFY_FAIL_ON_THROTTLED', true),

    // Max retry attempts when throttled
    'throttle_max_tries' => env('SHOPIFY_THROTTLE_MAX_TRIES', 5),
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Luminarix Labs](https://github.com/luminarix)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
