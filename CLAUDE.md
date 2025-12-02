# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel package providing a GraphQL client for Shopify's Admin API, built on Saloon HTTP client.

## Commands

```bash
composer test          # Run tests (Pest)
composer analyse       # Run PHPStan (level 9)
composer format        # Run Laravel Pint
```

Run a single test:
```bash
./vendor/bin/pest tests/ExampleTest.php
./vendor/bin/pest --filter="test name"
```

## Architecture

### Client Flow

1. `GraphQLClient::factory()` returns `GraphQLClientCreate`
2. `GraphQLClientCreate::create($authenticator)` returns `GraphQLClientMethods`
3. `GraphQLClientMethods` provides the API: `query()`, `mutate()`, bulk operations

### Key Components

**Core Classes** (`src/`):
- `GraphQLClientMethods`: Main API facade with `query()`, `mutate()`, and bulk operation methods
- `GraphQLClientTransformer`: Response wrapper with `toArray()`, `toCollection()`, `toDTO()` methods

**Services** (`src/Services/`):
- `RequestExecutor`: Centralized request handling with rate limit tracking and retry logic
- `ThrottleDetector`: Detects Shopify API throttling from responses
- `RedisRateLimitService` / `NullRateLimitService`: Rate limit tracking implementations
- `QueryTransformer`: GraphQL query pagination transformation

**Contracts** (`src/Contracts/`):
- `RateLimitable`: Interface for rate limit services
- `ThrottleDetectable`: Interface for throttle detection

**Data Objects** (`src/Data/`):
- `RateLimitState`: Immutable value object for rate limit information

**Enums** (`src/Enums/`):
- `ResponsePath`: Maps response types to their data extraction paths
- `GraphQLRequestType`: Query vs Mutation type

**GraphQL** (`src/GraphQL/`):
- `BulkOperationQueries`: Centralized GraphQL query templates for bulk operations

**Integrations** (`src/Integrations/`):
- `ShopifyConnector`: Saloon connector handling base URL, auth, and failure detection
- `Requests/`: Saloon request classes (Query, Mutation, BulkOperation, etc.)

**Authenticators** (`src/Authenticators/`):
- `ShopifyApp`: Handles shop domain and access token validation

### Design Patterns

- **Factory pattern**: `GraphQLClient::factory()->create($authenticator)`
- **Value objects**: `RateLimitState` for immutable rate limit data
- **Strategy pattern**: `RateLimitable` and `ThrottleDetectable` contracts allow swapping implementations
- **Template method**: Request classes extend `BaseRequest` with `defaultBody()` implementation

## Configuration

Published to `config/shopify-graphql.php`:
- `api_version`: Shopify API version (default: 2025-01)
- `fail_on_throttled`: Whether to fail immediately on throttle (default: true)
- `throttle_max_tries`: Retry attempts before failing (default: 5)

## Testing

Uses Pest with Orchestra Testbench. Tests extend `TestCase` which registers the service provider.
