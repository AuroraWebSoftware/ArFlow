<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Commands\ArFlowCommand;
use AuroraWebSoftware\ArFlow\Traits\HasState;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Traits\Macroable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ArFlowServiceProvider extends PackageServiceProvider
{
    public function boot(): ArFlowServiceProvider
    {
        Blueprint::macro('arflow', function (string $workflow = 'workflow', string $state = 'state', string $stateMetadata = 'state_metadata') {
            /**
             * @var Blueprint $this
             */
            $this->string($workflow)->nullable(false)->index();
            $this->string($state)->nullable(false)->index();
            $this->json($stateMetadata)->nullable(false);
        });

        return parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('arflow')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_arflow_table')
            ->hasCommand(ArFlowCommand::class);
    }
}
