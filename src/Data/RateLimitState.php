<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Data;

use JetBrains\PhpStorm\ArrayShape;

readonly class RateLimitState
{
    public function __construct(
        public float|int|null $requestedQueryCost = null,
        public float|int|null $actualQueryCost = null,
        public float|int|null $maximumAvailable = null,
        public float|int|null $currentlyAvailable = null,
        public float|int|null $restoreRate = null,
        public bool $isThrottled = false,
    ) {}

    public static function fromResponse(array $response, bool $isThrottled = false): self
    {
        return new self(
            requestedQueryCost: self::extractCost($response, 'requestedQueryCost'),
            actualQueryCost: self::extractCost($response, 'actualQueryCost'),
            maximumAvailable: self::extractCost($response, 'throttleStatus.maximumAvailable'),
            currentlyAvailable: self::extractCost($response, 'throttleStatus.currentlyAvailable'),
            restoreRate: self::extractCost($response, 'throttleStatus.restoreRate'),
            isThrottled: $isThrottled,
        );
    }

    #[ArrayShape([
        'requestedQueryCost' => 'float|int|null',
        'actualQueryCost' => 'float|int|null',
        'maxAvailableLimit' => 'float|int|null',
        'lastAvailableLimit' => 'float|int|null',
        'restoreRate' => 'float|int|null',
        'isThrottled' => 'bool',
    ])]
    public function toArray(): array
    {
        return [
            'requestedQueryCost' => $this->requestedQueryCost,
            'actualQueryCost' => $this->actualQueryCost,
            'maxAvailableLimit' => $this->maximumAvailable,
            'lastAvailableLimit' => $this->currentlyAvailable,
            'restoreRate' => $this->restoreRate,
            'isThrottled' => $this->isThrottled,
        ];
    }

    public function toServiceArray(): array
    {
        return [
            'maximumAvailable' => $this->maximumAvailable,
            'currentlyAvailable' => $this->currentlyAvailable,
            'restoreRate' => $this->restoreRate,
        ];
    }

    private static function extractCost(array $response, string $path): float|int|null
    {
        /** @var float|int|null $cost */
        $cost = data_get($response, "extensions.cost.{$path}");

        return $cost;
    }
}
