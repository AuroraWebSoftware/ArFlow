<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\DTOs\TransitionActionReturnDTO;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionActionException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use Illuminate\Database\Eloquent\Model;

interface TransitionActionContract
{
    public function boot(StateableModelContract&Model $model, $from, $to, ...$parameters);

    /**
     * @throws WorkflowNotSupportedException
     * @throws TransitionActionException
     * @throws WorkflowNotFoundException
     */
    public function handle(): TransitionActionReturnDTO;
}
