<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Integrations;

use Illuminate\Support\Arr;
use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;

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

    public function create(): ShopifyResource
    {
        return new ShopifyResource($this);
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        return Arr::exists($response->json(), 'errors');
    }

    protected function defaultAuth(): HeaderAuthenticator
    {
        return new HeaderAuthenticator($this->appAuthenticator->accessToken, 'X-Shopify-Access-Token');
    }
}
