<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Authenticators\Abstracts;

use Illuminate\Support\Str;
use InvalidArgumentException;

class AbstractAppAuthenticator
{
    public string $accessToken;

    private string $shopDomain;

    private ?string $apiVersion;

    public function setShopDomain(string $shopDomain): void
    {
        $shopDomain = preg_replace('#^https?://|/$#', '', $shopDomain);

        if (!Str::endsWith($shopDomain, '.myshopify.com')) {
            throw new InvalidArgumentException('Invalid shop URL. The shop URL must end with ".myshopify.com".');
        }

        $this->shopDomain = $shopDomain;
    }

    public function getShopDomain(): string
    {
        return $this->shopDomain;
    }

    public function setApiVersion(?string $apiVersion): void
    {
        if (!preg_match('/\d{4}-\d{2}/', $apiVersion)) {
            throw new InvalidArgumentException('Invalid API version. The API version must match the pattern "YYYY-MM".');
        }

        $this->apiVersion = $apiVersion;
    }

    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }
}
