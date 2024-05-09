<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Authenticators;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use SensitiveParameter;

class PublicApp extends AbstractAppAuthenticator
{
    public function __construct(
        string $shopUrl,
        #[SensitiveParameter] public string $accessToken,
        public ?string $clientId = null,
        #[SensitiveParameter] public ?string $clientSecret = null,
        ?string $apiVersion = null,
    ) {
        $this->setShopDomain($shopUrl);
        $this->clientId ??= config('shopify-graphql.client_id');
        $this->clientSecret ??= config('shopify-graphql.client_secret');
        $this->setApiVersion($apiVersion ?? config('shopify-graphql.api_version'));
    }
}
