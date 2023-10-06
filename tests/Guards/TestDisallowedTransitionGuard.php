<?php

namespace AuroraWebSoftware\ArFlow\Tests\Guards;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionGuardContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use Illuminate\Database\Eloquent\Model;

class TestDisallowedTransitionGuard implements TransitionGuardContract
{
    public function handle(): TransitionGuardResultDTO
    {
        return TransitionGuardResultDTO::build(TransitionGuardResultDTO::DISALLOWED)->addMessage('izin yok');
    }

    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters): void
    {
        // TODO: Implement boot() method.
    }
}
