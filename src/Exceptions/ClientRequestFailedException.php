<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Exceptions;

use Exception;
use Throwable;

class ClientRequestFailedException extends Exception
{
    public function __construct(string $message = '', int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
