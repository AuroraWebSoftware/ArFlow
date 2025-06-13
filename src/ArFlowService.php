<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Exceptions\StateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ArFlowService
{
    /** @var array<string, array{states: array<string>, initial_state: string, transitions?: array<string, array{from: string|array<string>, to: string|array<string>, guards?: array<array{0: class-string, 1?: array<string, mixed>}>, actions?: array<array{0: class-string, 1?: array<string, mixed>}>, success_metadata?: array<string, mixed>, success_jobs?: array<array{0: class-string, 1?: array<string, mixed>}>}>}> */
    private array $workflows;

    public function __construct()
    {
        $this->workflows = Config::get('arflow.workflows') ?? [];
    }

    /**
     * @return array<string>
     *
     * @throws WorkflowNotFoundException
     */
    public function getStates(string $workflow): array
    {
        foreach ($this->workflows as $workflowKey => $workflowValues) {
            if ($workflowKey == $workflow) {
                return $workflowValues['states'];
            }
        }
        throw new WorkflowNotFoundException;
    }

    /**
     * @return array<class-string>
     */
    public function getSupportedModelTypes(string $workflow): array
    {
        return [];
        // workflowun supportded olduğu model ler
        // https://github.com/spatie/laravel-model-info
        // todo akif
    }

    /**
     * @param  class-string  $modelType
     * @return Collection<int, Model>|null
     */
    public function getModelInstances(string $workflow, string $modelType): ?Collection
    {
        return null;
        // workflow u kullanan modeller
        // todo akif
    }
}
