# GraphQL client for Shopify.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/luminarix/laravel-shopify-graphql.svg?style=flat-square)](https://packagist.org/packages/luminarix/laravel-shopify-graphql)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/luminarix/laravel-shopify-graphql/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/luminarix/laravel-shopify-graphql/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/luminarix/laravel-shopify-graphql/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/luminarix/laravel-shopify-graphql/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/luminarix/laravel-shopify-graphql.svg?style=flat-square)](https://packagist.org/packages/luminarix/laravel-shopify-graphql)

This is a work in progress package to provide a GraphQL client for Shopify.

## Installation

You can install the package via composer:

```bash
composer require luminarix/laravel-shopify-graphql
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-shopify-graphql-config"
```

## Usage

```php
use Luminarix\Shopify\GraphQLClient\Facades\GraphQLClient;
use Luminarix\Shopify\GraphQLClient\Authenticators\ShopifyApp;

$graphql = GraphQLClient::factory();

$authenticator = new ShopifyApp($shopDomain, $accessToken, $apiVersion);

$client = $graphql->create($authenticator)

// Query
$query = 'query {
  node(id: "gid://shopify/Order/148977776") {
    id
    ... on Order {
      name
    }
  }
}';

$response = $client->query($query);

// Mutation
$mutation = 'mutation orderMarkAsPaid($input: OrderMarkAsPaidInput!) {
  orderMarkAsPaid(input: $input) {
    order {
      # Order fields
    }
    userErrors {
      field
      message
    }
  }
}';

$variables = [
  'input' => [
    'id' => 'gid://shopify/<objectName>/10079785100',
  ],
];

$response = $client->mutation($mutation, $variables);
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
