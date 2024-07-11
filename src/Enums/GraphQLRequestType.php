<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Enums;

enum GraphQLRequestType: string
{
    case QUERY = 'query';
    case MUTATION = 'mutation';
}
