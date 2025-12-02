<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Services;

use Luminarix\Shopify\GraphQLClient\Contracts\ThrottleDetectable;

class ThrottleDetector implements ThrottleDetectable
{
    private const THROTTLE_CODE = 'THROTTLED';

    public function isThrottled(array $response): bool
    {
        return data_get($response, 'errors.0.extensions.code') === self::THROTTLE_CODE;
    }

    public function getThrottleCode(): string
    {
        return self::THROTTLE_CODE;
    }
}
