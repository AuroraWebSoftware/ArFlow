<?php

namespace AuroraWebSoftware\ArFlow\Commands;

use Illuminate\Console\Command;

class ArFlowCommand extends Command
{
    public $signature = 'arflow';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
