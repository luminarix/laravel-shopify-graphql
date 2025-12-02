<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Services;

use Illuminate\Support\Arr;
use Luminarix\Shopify\GraphQLClient\Contracts\RateLimitable;
use Luminarix\Shopify\GraphQLClient\Contracts\ThrottleDetectable;
use Luminarix\Shopify\GraphQLClient\Data\RateLimitState;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientRequestFailedException;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;
use Saloon\Http\Request;
use Saloon\Http\Response;

class RequestExecutor
{
    private ?RateLimitState $lastRateLimitState = null;

    private int $tries = 0;

    public function __construct(
        private readonly ShopifyConnector $connector,
        private readonly RateLimitable $rateLimitService,
        private readonly ThrottleDetectable $throttleDetector,
    ) {}

    /**
     * @throws ClientRequestFailedException
     */
    public function execute(Request $request, bool $trackRateLimits = true): array
    {
        $response = $this->sendRequest($request);
        $this->validateResponse($response);

        $data = $this->wrapResponse($response);

        if ($trackRateLimits) {
            $this->processRateLimits($data);

            if ($this->shouldRetry()) {
                return $this->retryRequest($request);
            }
        }

        $this->resetTries();

        return $data;
    }

    public function getLastRateLimitState(): ?RateLimitState
    {
        return $this->lastRateLimitState;
    }

    private function sendRequest(Request $request): Response
    {
        return $this->connector->send($request);
    }

    /**
     * @throws ClientRequestFailedException
     */
    private function validateResponse(Response $response): void
    {
        throw_if($response->failed(), ClientRequestFailedException::class, $response);
    }

    private function wrapResponse(Response $response): array
    {
        return Arr::wrap($response->json());
    }

    private function processRateLimits(array $response): void
    {
        $isThrottled = $this->throttleDetector->isThrottled($response);

        $this->lastRateLimitState = RateLimitState::fromResponse($response, $isThrottled);

        $this->rateLimitService->updateRateLimitInfo(
            $this->lastRateLimitState->toServiceArray()
        );
    }

    private function shouldRetry(): bool
    {
        if ($this->lastRateLimitState === null) {
            return false;
        }

        if (!$this->lastRateLimitState->isThrottled) {
            return false;
        }

        /** @var int $maxTries */
        $maxTries = config('shopify-graphql.throttle_max_tries', 5);

        return $this->tries < $maxTries;
    }

    /**
     * @throws ClientRequestFailedException
     */
    private function retryRequest(Request $request): array
    {
        $this->tries++;

        /** @var RateLimitState $rateLimitState */
        $rateLimitState = $this->lastRateLimitState;
        $requestedCost = $rateLimitState->requestedQueryCost ?? 0;

        $this->rateLimitService->waitIfNecessary((float)$requestedCost);

        return $this->execute($request, trackRateLimits: true);
    }

    private function resetTries(): void
    {
        $this->tries = 0;
    }
}
