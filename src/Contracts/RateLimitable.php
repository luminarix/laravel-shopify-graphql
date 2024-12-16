<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Contracts;

interface RateLimitable
{
    public function getRateLimitInfo(): array;

    public function updateRateLimitInfo(array $data): void;

    public function calculateWaitTime(float $requestedQueryCost): float;

    public function waitIfNecessary(float $requestedQueryCost): void;
}
