<?php

namespace AuroraWebSoftware\ArFlow\Tests\Actions;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionActionContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionActionResultDTO;
use Illuminate\Database\Eloquent\Model;

class TestSuccessTransitionAction implements TransitionActionContract
{

    public function boot(StateableModelContract&Model $model, $from, $to, ...$parameters)
    {
        // TODO: Implement boot() method.
    }

    /**
     * @inheritDoc
     */
    public function handle(): TransitionActionResultDTO
    {
        // TODO: Implement handle() method.
    }
}