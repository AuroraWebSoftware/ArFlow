<?php

namespace AuroraWebSoftware\ArFlow\Tests\Jobs;

use AuroraWebSoftware\ArFlow\Abstracts\AbstractTransitionSuccessJob;
use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use Illuminate\Database\Eloquent\Model;

class TestTransitionSuccessJob extends AbstractTransitionSuccessJob
{
    /**
     * Execute the job.
     */
    public function handle(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void
    {
    }
}
