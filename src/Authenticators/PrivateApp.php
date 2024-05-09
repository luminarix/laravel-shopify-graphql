<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Authenticators;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use SensitiveParameter;

class PrivateApp extends AbstractAppAuthenticator
{
    public function __construct(
        string $shopDomain,
        #[SensitiveParameter] public string $accessToken,
        public string $apiKey,
        #[SensitiveParameter] public string $apiSecretKey,
        ?string $apiVersion = null,
    ) {
        $this->setShopDomain($shopDomain);
        $this->setApiVersion($apiVersion ?? config('shopify-graphql.api_version'));
    }
}
