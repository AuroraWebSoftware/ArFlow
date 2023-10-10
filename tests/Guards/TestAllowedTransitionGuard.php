<?php

namespace AuroraWebSoftware\ArFlow\Tests\Guards;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionGuardContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Logger;

class TestAllowedTransitionGuard implements TransitionGuardContract
{
    public function __construct(public Logger $logger)
    {
    }

    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters): void
    {
        $this->logger->alert('TestAllowedTransitionGuard boot');
        // TODO: Implement boot() method.
    }

    public function handle(): TransitionGuardResultDTO
    {
        return TransitionGuardResultDTO::build(TransitionGuardResultDTO::ALLOWED);
    }
}
