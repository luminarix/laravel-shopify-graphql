<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use JetBrains\PhpStorm\ArrayShape;
use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientNotInitializedException;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientRequestFailedException;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;
use Luminarix\Shopify\GraphQLClient\Services\RateLimitService;

class GraphQLClientMethods
{
    use Macroable;

    private RateLimitService $rateLimitService;

    private float|int|null $requestedQueryCost = null;

    private float|int|null $actualQueryCost = null;

    private float|int|null $maximumAvailable = null;

    private float|int|null $currentlyAvailable = null;

    private float|int|null $restoreRate = null;

    private bool $isThrottled = false;

    private int $tries = 0;

    public function __construct(
        private readonly AbstractAppAuthenticator $appAuthenticator,
        private ?ShopifyConnector $connector = null,
    ) {
        $this->connector = new ShopifyConnector($this->appAuthenticator);
        $this->rateLimitService = new RateLimitService($this->appAuthenticator->getShopDomain());
    }

    /**
     * @throws ClientNotInitializedException If the connector is not set
     * @throws ClientRequestFailedException If the response contains errors
     */
    public function query(string $query, bool $withExtensions = false, bool $detailedCost = false): GraphQLClientTransformer
    {
        $response = $this->makeQueryRequest($query, $withExtensions, $detailedCost);

        /** @var array $response */
        $response = $withExtensions ? $response : data_get($response, 'data');

        return new GraphQLClientTransformer(
            data: array_filter($response)
        );
    }

    /**
     * @param  array<mixed, mixed>  $variables
     *
     * @throws ClientNotInitializedException If the connector is not set
     * @throws ClientRequestFailedException If the response contains errors
     */
    public function mutate(string $query, array $variables, bool $withExtensions = false, bool $detailedCost = false): GraphQLClientTransformer
    {
        $response = $this->makeMutationRequest($query, $variables, $withExtensions, $detailedCost);

        /** @var array $response */
        $response = $withExtensions ? $response : data_get($response, 'data');

        return new GraphQLClientTransformer(
            data: array_filter($response)
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
    public function getRateLimitInfo(): array
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

    /**
     * @throws ClientNotInitializedException
     * @throws ClientRequestFailedException
     */
    private function makeQueryRequest(string $query, bool $withExtensions = false, bool $detailedCost = false): array
    {
        throw_if($this->connector === null, ClientNotInitializedException::class);

        $response = $this->connector->create()->query($query, $detailedCost);

        throw_if($response->failed(), ClientRequestFailedException::class, $response);

        $response = Arr::wrap($response->json());

        $this->ispectResponse($response);

        if ($this->isThrottled) {
            if ($this->tries < config('shopify-graphql.throttle_max_tries')) {
                $this->tries++;

                $this->rateLimitService->waitIfNecessary((float)$this->requestedQueryCost);

                return $this->makeQueryRequest($query, $withExtensions, $detailedCost);
            }

            throw_if(true, ClientRequestFailedException::class, $response);
        }

        return $response;
    }

    /**
     * @throws ClientNotInitializedException
     * @throws ClientRequestFailedException
     */
    private function makeMutationRequest(string $query, array $variables, bool $withExtensions = false, bool $detailedCost = false): array
    {
        throw_if($this->connector === null, ClientNotInitializedException::class);

        $response = $this->connector->create()->mutation($query, $variables, $detailedCost);

        throw_if($response->failed(), ClientRequestFailedException::class, $response);

        $response = Arr::wrap($response->json());

        $this->ispectResponse($response);

        if ($this->isThrottled) {
            if ($this->tries < config('shopify-graphql.throttle_max_tries')) {
                $this->tries++;

                $this->rateLimitService->waitIfNecessary((float)$this->requestedQueryCost);

                return $this->makeMutationRequest($query, $variables, $withExtensions, $detailedCost);
            }

            throw_if(true, ClientRequestFailedException::class, $response);
        }

        return $response;
    }

    private function ispectResponse(
        array $response,
    ): void {
        $this->updateRateLimitInfo($response);
    }

    private function updateRateLimitInfo(array $response): void
    {
        $this->requestedQueryCost = $this->getCost($response, 'requestedQueryCost');
        $this->actualQueryCost = $this->getCost($response, 'actualQueryCost');
        $this->maximumAvailable = $this->getCost($response, 'throttleStatus.maximumAvailable');
        $this->currentlyAvailable = $this->getCost($response, 'throttleStatus.currentlyAvailable');
        $this->restoreRate = $this->getCost($response, 'throttleStatus.restoreRate');
        $this->isThrottled = $this->isThrottled($response);

        $rateLimitData = [
            'maximumAvailable' => $this->maximumAvailable,
            'currentlyAvailable' => $this->currentlyAvailable,
            'restoreRate' => $this->restoreRate,
        ];
        $this->rateLimitService->updateRateLimitInfo($rateLimitData);
    }

    private function getCost(array $response, string $costType): float|int|null
    {
        /** @var float|int|null $cost */
        $cost = data_get($response, "extensions.cost.{$costType}");

        return $cost;
    }

    private function isThrottled(array $response): bool
    {
        return data_get($response, 'errors.0.extensions.code') === 'THROTTLED';
    }
}
