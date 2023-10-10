<?php

namespace AuroraWebSoftware\ArFlow\Facades;

use AuroraWebSoftware\ArFlow\ArFlowService;
use Illuminate\Support\Facades\Facade;

/**
 * @see ArFlowService
 * @method static array<string> getStates(string $workflow)
 */
class ArFlow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ArFlowService::class;
    }
}
