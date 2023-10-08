<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\Exceptions\TransitionActionException;
use Exception;
use Illuminate\Database\Eloquent\Model;

interface TransitionActionContract
{
    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void;

    /**
     * @throws TransitionActionException
     * @throws Exception
     */
    public function handle(): void;
    public function failed(): void;
}
