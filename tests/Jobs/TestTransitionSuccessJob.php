<?php

namespace AuroraWebSoftware\ArFlow\Tests\Jobs;

use AuroraWebSoftware\ArFlow\Abstracts\AbstractTransitionSuccessJob;
use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestTransitionSuccessJob extends AbstractTransitionSuccessJob
{

    public function __construct(StateableModelContract&Model $model,string $from,string $to,array $parameters = [], array $metadata = [])
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        echo "Success Job";
    }
}
