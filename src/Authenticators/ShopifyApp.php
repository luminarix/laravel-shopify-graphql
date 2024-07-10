<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Authenticators;

use Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts\AbstractAppAuthenticator;
use SensitiveParameter;

class ShopifyApp extends AbstractAppAuthenticator
{
    public function __construct(
        string $shopDomain,
        #[SensitiveParameter] public string $accessToken,
        ?string $apiVersion = null,
    ) {
        $this->setShopDomain($shopDomain);

        /** @var string $configApiVersion */
        $configApiVersion = config('shopify-graphql.api_version');
        $this->setApiVersion($apiVersion ?? $configApiVersion);
    }
}
