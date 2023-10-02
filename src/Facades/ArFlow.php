<?php

namespace AuroraWebSoftware\ArFlow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AuroraWebSoftware\ArFlow\ArFlow
 */
class ArFlow extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \AuroraWebSoftware\ArFlow\ArFlow::class;
    }
}
