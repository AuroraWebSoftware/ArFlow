<?php

namespace AuroraWebSoftware\ArFlow\Tests\Guards;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionGuardContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardReturnDTO;
use Illuminate\Database\Eloquent\Model;

class TestAllowedTransitionGuard implements TransitionGuardContract
{
    public function boot(StateableModelContract&Model $model, $from, $to, ...$parameters)
    {
        // TODO: Implement boot() method.
    }

    public function handle(): TransitionGuardReturnDTO
    {
        return TransitionGuardReturnDTO::build(TransitionGuardReturnDTO::DISALLOWED)->addMessage('izin yok');
    }
}
