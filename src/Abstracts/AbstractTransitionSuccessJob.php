<?php

namespace AuroraWebSoftware\ArFlow\Abstracts;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Exceptions\NotImplementedException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class AbstractTransitionSuccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    /**
     * @throws NotImplementedException
     */
    public function handle(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void
    {
        throw new NotImplementedException();
    }
}
