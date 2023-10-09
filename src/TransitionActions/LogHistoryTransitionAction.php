<?php

namespace AuroraWebSoftware\ArFlow\TransitionActions;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionActionContract;
use Illuminate\Database\Eloquent\Model;

class LogHistoryTransitionAction implements TransitionActionContract
{
    public array $parameters;

    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    public function handle(): void
    {
        return;
    }

    public function failed(): void
    {
        // TODO: Implement failed() method.
    }
}
