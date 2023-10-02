<?php

namespace AuroraWebSoftware\ArFlow;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AuroraWebSoftware\ArFlow\Commands\ArFlowCommand;

class ArFlowServiceProvider extends PackageServiceProvider
{
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
