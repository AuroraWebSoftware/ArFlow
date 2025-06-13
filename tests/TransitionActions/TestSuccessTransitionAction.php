<?php

namespace AuroraWebSoftware\ArFlow\Tests\TransitionActions;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionActionContract;
use Illuminate\Database\Eloquent\Model;

class TestSuccessTransitionAction implements TransitionActionContract
{
    /** @var array<string, mixed> */
    public array $parameters;

    /**
     * @param array<string, mixed> $parameters
     */
    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    public function handle(): void {}

    public function failed(): void
    {
        // TODO: Implement failed() method.
    }
}
