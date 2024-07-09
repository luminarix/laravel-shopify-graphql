<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientNotInitializedException;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientRequestFailedException;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;

class GraphQLClientMethods
{
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

        return new GraphQLClientTransformer(
            data: $withExtensions ? $response->json() : $response->json()['data']
        );
    }

    /**
     * @throws ClientNotInitializedException If the connector is not set
     * @throws ClientRequestFailedException If the response contains errors
     */
    public function mutate(string $query, array $variables, bool $withExtensions = false, bool $detailedCost = false): GraphQLClientTransformer
    {
        throw_if($this->connector === null, ClientNotInitializedException::class);

        $response = $this->connector->create()->mutation($query, $variables, $detailedCost);

        throw_if($response->failed(), ClientRequestFailedException::class, $response);

        return new GraphQLClientTransformer(
            data: $withExtensions ? $response->json() : $response->json()['data']
        );
    }
}
