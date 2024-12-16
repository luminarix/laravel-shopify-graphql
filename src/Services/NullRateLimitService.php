<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Services;

use Luminarix\Shopify\GraphQLClient\Contracts\RateLimitable;

readonly class NullRateLimitService implements RateLimitable
{
    public function getRateLimitInfo(): array
    {
        return [];
    }

    public function updateRateLimitInfo(array $data): void {}

    public function calculateWaitTime(float $requestedQueryCost): float
    {
        return 0.0;
    }

    public function waitIfNecessary(float $requestedQueryCost): void {}
}
