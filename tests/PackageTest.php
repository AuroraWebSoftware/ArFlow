<?php

use AuroraWebSoftware\ArFlow\Tests\Models\Stateable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    //$seeder = new SampleDataSeeder();
    // $seeder->run();
    // $this->service = new OrganizationService();

    \Illuminate\Support\Facades\Config::set(
        [
            'arflow' => [
                'workflows' => [
                    'workflow1' => [
                        'states' => ['a', 'b'],
                        'initial_state' => 'a',
                        'transitions' => [
                            'transtion1' => [
                                'from' => ['a'],
                                'to' => 'b',
                                'guard' => [
                                    [\AuroraWebSoftware\ArFlow\Tests\Guards\TestAllowedTransitionGuard::class, ['permission' => 'represtative_approval']],
                                ],
                                'action' => [
                                    [\AuroraWebSoftware\ArFlow\Tests\Actions\TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'successMetadata' => ['asd' => 'asd'],
                                'successJob' => [],
                                'failMetadata' => [],
                                'failJob' => [],
                            ],
                        ],

                    ],
                ],
            ],
        ]
    );

    Schema::create('stateables', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->arflow();
        $table->timestamps();
    });

    /*
    $this->app->singleton('aauth', function ($app) {
        return new \AuroraWebSoftware\AAuth\AAuth(
            User::find(1),
            3
        );
    });
    */
});

it('can create a stateable model instance', function () {

    $name = 'model name';

    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $this->assertEquals($modelInstance->name, $name);

    $this->assertEquals(Stateable::where('name', $name)->first()->name, $name);
});

it('a', function () {

    $name = 'model name';

    /**
     * @var \AuroraWebSoftware\ArFlow\Contacts\StateableModelContract $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->canTransitionTo('a');

});

it('can create a stateable model instances', function () {

    $modelInstance = Stateable::create(
        ['name' => 'a']
    );

    /**
     * @var \Illuminate\Database\Eloquent\Model&\AuroraWebSoftware\ArFlow\Contacts\StateableModelContract $modelInstance
     */
    $modelInstance->applyWorkflow('a');

    $modelInstance->canTransitionTo('b');

    $modelInstance->possibleTransitions();

    $modelInstance->transitionTo('b');

    $modelInstance->currentState();
});
