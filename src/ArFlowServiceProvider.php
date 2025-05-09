<?php

namespace AuroraWebSoftware\ArFlow;

use AuroraWebSoftware\ArFlow\Commands\ArFlowCommand;
use Illuminate\Database\Schema\Blueprint;
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
            $this->string($workflow)->nullable()->index();
            $this->string($state)->nullable()->index();
            $this->json($stateMetadata)->nullable();
        });

        Blueprint::macro('arflowDown', function (string $workflow = 'workflow', string $state = 'state', string $stateMetadata = 'state_metadata') {
            /**
             * @var Blueprint $this
             */
            $this->dropColumn($workflow);
            $this->dropColumn($state);
            $this->dropColumn($stateMetadata);
        });

        // load packages migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

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
            ->hasConfigFile('arflow')
            ->hasViews()
            // ->hasMigration('create_arflow_history_table')
            ->hasCommand(ArFlowCommand::class);

    }
}
