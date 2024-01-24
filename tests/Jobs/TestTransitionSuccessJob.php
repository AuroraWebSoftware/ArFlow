<?php

namespace AuroraWebSoftware\ArFlow\Tests\Jobs;

use AuroraWebSoftware\ArFlow\Abstracts\AbstractTransitionSuccessJob;
use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use Illuminate\Database\Eloquent\Model;

class TestTransitionSuccessJob extends AbstractTransitionSuccessJob
{
    public function __construct(StateableModelContract&Model $model, string $from, string $to, array $parameters = [], array $metadata = [])
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        echo 'Success Job';
    }
}
