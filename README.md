# GraphQL client for Shopify.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/luminarix/laravel-shopify-graphql.svg?style=flat-square)](https://packagist.org/packages/luminarix/laravel-shopify-graphql)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/luminarix/laravel-shopify-graphql/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/luminarix/laravel-shopify-graphql/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/luminarix/laravel-shopify-graphql/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/luminarix/laravel-shopify-graphql/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/luminarix/laravel-shopify-graphql.svg?style=flat-square)](https://packagist.org/packages/luminarix/laravel-shopify-graphql)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require luminarix/laravel-shopify-graphql
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-shopify-graphql-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-shopify-graphql-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-shopify-graphql-views"
```

## Usage

```php
$graphQLClient = new Luminarix\Shopify\GraphQLClient();
echo $graphQLClient->echoPhrase('Hello, Luminarix!');
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
