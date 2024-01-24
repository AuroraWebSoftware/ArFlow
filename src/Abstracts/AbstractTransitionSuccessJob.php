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

    /**
     * @param  array  $metadata
     */
    public function __construct(StateableModelContract&Model $model, string $from, string $to, array $parameters = [])
    {
    }

    /**
     * @throws NotImplementedException
     */
    public function handle(): void
    {
        throw new NotImplementedException();
    }
}
