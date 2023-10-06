<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use Illuminate\Database\Eloquent\Model;

interface TransitionGuardContract
{
    public function boot(StateableModelContract & Model $model, string $from, string $to, array $parameters): void;

    /**
     * @return TransitionGuardResultDTO
     * @throws WorkflowNotSupportedException
     * @throws WorkflowNotFoundException
     */
    public function handle(): TransitionGuardResultDTO;

}