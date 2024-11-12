<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Exceptions\StateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ArFlowService
{
    private array $workflows;

    public function __construct()
    {
        $this->workflows = Config::get('arflow.workflows') ?? [];
    }

    /**
     * @return array<string>
     *
     * @throws WorkflowNotFoundException
     * @throws StateNotFoundException
     */
    public function getStates(string $workflow): array
    {
        foreach ($this->workflows as $workflowKey => $workflowValues) {
            if ($workflowKey == $workflow) {
                return $workflowValues['states'] ?? throw new StateNotFoundException;
            }
        }
        throw new WorkflowNotFoundException;
    }

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
