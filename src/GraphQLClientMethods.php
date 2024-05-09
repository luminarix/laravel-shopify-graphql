<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Luminarix\Shopify\GraphQLClient\Exceptions\ClientNotInitializedException;
use Luminarix\Shopify\GraphQLClient\Integrations\ShopifyConnector;

class GraphQLClientMethods
{
    public function __construct(
        private readonly AbstractAppAuthenticator $appAuthenticator,
        private ?ShopifyConnector $connector = null,
    ) {
        $this->connector = new ShopifyConnector($this->appAuthenticator);
    }

    public function query(string $query): array
    {
        if ($this->connector === null) {
            throw new ClientNotInitializedException('Connector is not set');
        }

        return $this->connector->create()->query($query)->json();
    }

    public function mutate(string $query, array $variables): array
    {
        if ($this->connector === null) {
            throw new ClientNotInitializedException('Connector is not set');
        }

        return $this->connector->create()->mutation($query, $variables)->json();
    }
}
