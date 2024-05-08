<?php

namespace Luminarix\Skeleton\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Luminarix\Skeleton\Skeleton
 */
class Skeleton extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Luminarix\Skeleton\Skeleton::class;
    }
}
