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

    public function __construct(
        public StateableModelContract&Model $model,
        public string $from,
        public string $to,
        public array $parameters = [])
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
