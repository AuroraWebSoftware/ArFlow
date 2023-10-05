<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardReturnDTO;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use Illuminate\Database\Eloquent\Model;

interface TransitionGuardContract
{
    public function boot(StateableModelContract & Model $model, $from, $to, ...$parameters);

    /**
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     * @return TransitionGuardReturnDTO
     */
    public function handle(): TransitionGuardReturnDTO;

}