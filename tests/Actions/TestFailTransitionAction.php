<?php

namespace AuroraWebSoftware\ArFlow\Tests\Actions;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionActionContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionActionResultDTO;
use Illuminate\Database\Eloquent\Model;

class TestFailTransitionAction implements TransitionActionContract
{
    public array $parameters;

    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    public function handle(): TransitionActionResultDTO
    {
        return TransitionActionResultDTO::build(TransitionActionResultDTO::FAIL);
    }
}
