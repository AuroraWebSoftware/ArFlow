<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use Illuminate\Database\Eloquent\Model;

class ArFlow
{
    public function __construct(private Model&StateableModelContract $modelInstance)
    {
        $this->modelInstance->currentWorkflow();
        $this->modelInstance->currentState();
    }

    public function canTransitionTo(string $state, array $withoutGuards = null): bool
    {
        $workwlowsconfig('arflow.workflows');

    }
}
