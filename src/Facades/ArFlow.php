<?php

namespace AuroraWebSoftware\ArFlow\Facades;

use AuroraWebSoftware\ArFlow\ArFlowService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @see ArFlowService
 * @method Collection getModelInstances(string $workflow, string $modelType)
 * @method array getSupportedModelTypes(string $workflow,Collection $models)
 * @method static array<string> getStates(string $workflow)
 */
class ArFlow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ArFlowService::class;
    }
}
