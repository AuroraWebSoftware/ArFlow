<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use Illuminate\Database\Eloquent\Model;
use function Symfony\Component\Translation\t;

class ArFlow
{
    public function __construct(private Model&StateableModelContract $modelInstance)
    {
        $this->modelInstance->appliedWorkflow();
        $this->modelInstance->currentState();
    }

    public function canTransitionTo() {

    }


}
