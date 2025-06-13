<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use Illuminate\Database\Eloquent\Model;

interface TransitionGuardContract
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters): void;

    /**
     * @throws WorkflowNotSupportedException
     * @throws WorkflowNotFoundException
     */
    public function handle(): TransitionGuardResultDTO;
}
