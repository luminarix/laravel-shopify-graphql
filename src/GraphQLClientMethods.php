<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Traits\Macroable;
use JetBrains\PhpStorm\ArrayShape;
use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Enums\GraphQLRequestType;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientNotInitializedException;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientRequestFailedException;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;

class GraphQLClientMethods
{
    use Macroable;

    private float|int|null $maxAvailableLimit = null;

    private float|int|null $lastAvailableLimit = null;

    private float|int|null $restoreRate = null;

    public function __construct(
        private readonly AbstractAppAuthenticator $appAuthenticator,
        private ?ShopifyConnector $connector = null,
    ) {
        $this->connector = new ShopifyConnector($this->appAuthenticator);
    }

    /**
     * @throws ClientNotInitializedException If the connector is not set
     * @throws ClientRequestFailedException If the response contains errors
     */
    public function query(string $query, bool $withExtensions = false, bool $detailedCost = false): GraphQLClientTransformer
    {
        throw_if($this->connector === null, ClientNotInitializedException::class);

        $response = $this->connector->create()->query($query, $detailedCost);

        throw_if($response->failed(), ClientRequestFailedException::class, $response);

        $response = $response->json();

        $this->ispectResponse(
            type: GraphQLRequestType::QUERY,
            response: $response,
            query: $query,
            withExtensions: $withExtensions,
            detailedCost: $detailedCost
        );

        return new GraphQLClientTransformer(
            data: array_filter(Arr::wrap(
                value: $withExtensions ? $response : data_get($response, 'data')
            ))
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
        throw_if($this->connector === null, ClientNotInitializedException::class);

        $response = $this->connector->create()->mutation($query, $variables, $detailedCost);

        throw_if($response->failed(), ClientRequestFailedException::class, $response);

        $response = $response->json();

        $this->ispectResponse(
            type: GraphQLRequestType::MUTATION,
            response: $response,
            query: $query,
            withExtensions: $withExtensions,
            detailedCost: $detailedCost,
            variables: $variables
        );

        return new GraphQLClientTransformer(
            data: array_filter(
                Arr::wrap(
                    value: $withExtensions ? $response : data_get($response, 'data')
                )
            )
        );
    }

    #[ArrayShape([
        'maxAvailableLimit' => 'float|int|null',
        'lastAvailableLimit' => 'float|int|null',
        'restoreRate' => 'float|int|null',
    ])]
    public function getRateLimitInfo(): array
    {
        return [
            'maxAvailableLimit' => $this->maxAvailableLimit,
            'lastAvailableLimit' => $this->lastAvailableLimit,
            'restoreRate' => $this->restoreRate,
        ];
    }

    private function ispectResponse(
        GraphQLRequestType $type,
        mixed $response,
        string $query,
        bool $withExtensions,
        bool $detailedCost,
        array $variables = [],
    ): void {
        if (!is_array($response)) {
            return;
        }

        /** @var float|int|null $requestedQueryCost */
        $requestedQueryCost = data_get($response, 'extensions.cost.requestedQueryCost');
        /** @var float|int|null $actualQueryCost */
        $actualQueryCost = data_get($response, 'extensions.cost.actualQueryCost');

        /** @var float|int|null $maxAvailableLimit */
        $maxAvailableLimit = data_get($response, 'extensions.cost.throttleStatus.maximumAvailable');
        /** @var float|int|null $lastAvailableLimit */
        $lastAvailableLimit = data_get($response, 'extensions.cost.throttleStatus.currentlyAvailable');
        /** @var float|int|null $restoreRate */
        $restoreRate = data_get($response, 'extensions.cost.throttleStatus.restoreRate');

        $this->updateRateLimitInfo($maxAvailableLimit, $lastAvailableLimit, $restoreRate);

        $context = [
            'type' => $type->value,
            'query' => $query,
            'variables' => $variables,
            'withExtensions' => $withExtensions,
            'detailedCost' => $detailedCost,
            'requestedQueryCost' => $requestedQueryCost,
            'actualQueryCost' => $actualQueryCost,
        ];

        if (
            $requestedQueryCost !== null &&
            $requestedQueryCost > 900
        ) {
            Log::warning(
                message: "The requested query cost is high: {$requestedQueryCost}\nConsider optimizing the query (see context).",
                context: $context
            );
        }

        if (
            $actualQueryCost !== null &&
            $actualQueryCost > 900
        ) {
            Log::warning(
                message: "The actual query cost is high: {$actualQueryCost}\nConsider optimizing the query (see context).",
                context: $context
            );
        }
    }

    private function updateRateLimitInfo(float|int|null $maxAvailable, float|int|null $lastAvailable, float|int|null $restoreRate): void
    {
        $this->maxAvailableLimit = $maxAvailable;
        $this->lastAvailableLimit = $lastAvailable;
        $this->restoreRate = $restoreRate;
    }
}
