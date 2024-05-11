<?php

declare(strict_types=1);

namespace Luminarix\Shopify\GraphQLClient\Exceptions;

use Exception;
use Throwable;

class ClientNotInitializedException extends Exception
{
    public function __construct(string $message = 'Connector is not set', int $code = 428, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
