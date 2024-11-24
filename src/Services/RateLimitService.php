<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Sleep;

class RateLimitService
{
    private string $redisKey;

    public function __construct(private readonly string $shopDomain)
    {
        $this->redisKey = "shopify_graphql_rate_limit_{$this->shopDomain}";
    }

    public function getRateLimitInfo(): array
    {
        $rateLimit = Redis::get($this->redisKey);

        if (is_string($rateLimit)) {
            return Arr::wrap(json_decode($rateLimit, true));
        }

        // Initialize with default values if not set
        return [
            'maximumAvailable' => null,
            'currentlyAvailable' => null,
            'restoreRate' => null,
            'lastUpdated' => now()->timestamp,
        ];
    }

    public function updateRateLimitInfo(array $data): void
    {
        // Use Redis transactions to ensure atomicity
        // @phpstan-ignore staticMethod.notFound
        Redis::transaction(function ($tx) use ($data) {
            $currentInfo = $this->getRateLimitInfo();

            $updatedInfo = array_merge($currentInfo, $data);
            $updatedInfo['lastUpdated'] = now()->timestamp;

            $tx->set($this->redisKey, json_encode($updatedInfo));
        });
    }

    public function calculateWaitTime(float $requestedQueryCost): float
    {
        $rateLimitInfo = $this->getRateLimitInfo();

        // Check if rate limit information is available
        if (
            $rateLimitInfo['maximumAvailable'] === null ||
            $rateLimitInfo['currentlyAvailable'] === null ||
            $rateLimitInfo['restoreRate'] === null
        ) {
            // If not, return a default wait time or handle accordingly
            return 1.0; // Default to 1 second wait time
        }

        // Recalculate the currently available quota based on time elapsed
        $elapsedTime = now()->timestamp - $rateLimitInfo['lastUpdated'];
        $restoredQuota = $elapsedTime * $rateLimitInfo['restoreRate'];

        $currentlyAvailable = min(
            $rateLimitInfo['currentlyAvailable'] + $restoredQuota,
            $rateLimitInfo['maximumAvailable']
        );

        if ($requestedQueryCost <= $currentlyAvailable) {
            return 0;
        }

        // Calculate wait time
        $costToWaitFor = $requestedQueryCost - $currentlyAvailable;
        $waitTime = $costToWaitFor / $rateLimitInfo['restoreRate'];

        // Add a small buffer to be safe
        $waitTime += 0.1;

        return $waitTime;
    }

    public function waitIfNecessary(float $requestedQueryCost): void
    {
        $waitTime = $this->calculateWaitTime($requestedQueryCost);

        if ($waitTime > 0) {
            // Sleep for the calculated wait time
            Sleep::sleep($waitTime);
        }
    }
}
