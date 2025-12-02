<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Contracts;

interface ThrottleDetectable
{
    public function isThrottled(array $response): bool;

    public function getThrottleCode(): string;
}
