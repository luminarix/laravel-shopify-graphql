<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Connector;

class ShopifyConnector extends Connector
{
    public function __construct(
        public AbstractAppAuthenticator $appAuthenticator,
    ) {
    }

    /**
     * The base URL for the Shopify GraphQL API.
     */
    public function resolveBaseUrl(): string
    {
        return "https://{$this->appAuthenticator->getShopDomain()}/admin/api/{$this->appAuthenticator->getApiVersion()}";
    }

    protected function defaultAuth(): HeaderAuthenticator
    {
        return new HeaderAuthenticator($this->appAuthenticator->accessToken, 'X-Shopify-Access-Token');
    }

    public function create(): ShopifyResource
    {
        return new ShopifyResource($this);
    }
}
