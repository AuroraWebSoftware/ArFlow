<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Exceptions\StateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use Illuminate\Database\Eloquent\Collection;
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
                return $workflowValues['states'] ?? throw new StateNotFoundException();
            }
        }
        throw new WorkflowNotFoundException();
    }

    public function getSupportedModelTypes(string $workflow, $models): array
    {
        $modelClasses = $models;

        $supportedModelTypes = [];

        foreach ($modelClasses as $modelClass) {
            if (in_array(StateableModelContract::class, class_implements($modelClass))) {
                if (in_array($workflow, $modelClass::supportedWorkflows())) {
                    $supportedModelTypes[] = $modelClass;
                }
            }
        }

        return $supportedModelTypes;

    }

    public function getModelInstances(string $workflow, string $modelType): Collection
    {
        return $modelType::where('workflow', $workflow)->get();
    }

    public function getTestSupportDirectory(string $suffix = ''): string
    {
        return __DIR__.$suffix;
    }

    public function getTestDirectory(): string
    {
        return realpath($this->getTestSupportDirectory('/..'));
    }
}
