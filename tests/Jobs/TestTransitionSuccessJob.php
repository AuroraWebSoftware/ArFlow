<?php

namespace AuroraWebSoftware\ArFlow\Tests\Jobs;

use AuroraWebSoftware\ArFlow\Abstracts\AbstractTransitionSuccessJob;
use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use Illuminate\Database\Eloquent\Model;

class TestTransitionSuccessJob extends AbstractTransitionSuccessJob
{
    public function __construct(
        public StateableModelContract&Model $model,
        public string $from,
        public string $to,
        public array $parameters = []) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        echo 'Success Job';
    }
}
