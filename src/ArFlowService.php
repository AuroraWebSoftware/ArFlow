<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Exceptions\StateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Spatie\ModelInfo\ModelFinder;

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

    public function getSupportedModelTypes(string $workflow): array
    {
        $modelClasses = ModelFinder::all(
            $this->getTestSupportDirectory(),
            $this->getTestDirectory(),
            "AuroraWebSoftware\ArFlow",
        );

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

    /**
     * @param string $workflow
     * @param string $modelType
     * @return Collection
     */
    public function getModelInstances(string $workflow, string $modelType): Collection
    {
        return $modelType::where('workflow', $workflow)->get();
    }

    /**
     * @param string $suffix
     * @return string
     */
    public function getTestSupportDirectory(string $suffix = ''): string
    {
        return __DIR__.$suffix;
    }

    /**
     * @return string|false
     */
    public function getTestDirectory(): string|false
    {
        return realpath($this->getTestSupportDirectory('/..'));
    }
}
