<?php

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use AuroraWebSoftware\ArFlow\Tests\Actions\TestSuccessTransitionAction;
use AuroraWebSoftware\ArFlow\Tests\Guards\TestAllowedTransitionGuard;
use AuroraWebSoftware\ArFlow\Tests\Guards\TestDisallowedTransitionGuard;
use AuroraWebSoftware\ArFlow\Tests\Models\Stateable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    // $seeder = new SampleDataSeeder();
    // $seeder->run();
    // $this->service = new OrganizationService();

    Config::set(
        [
            'arflow' => [
                'workflows' => [
                    'workflow1' => [
                        'states' => ['todo', 'b'],
                        'initial_state' => 'todo',
                        'transitions' => [
                            'transtion1' => [
                                'from' => ['todo'],
                                'to' => 'in_progress',
                                'guards' => [
                                    [TestAllowedTransitionGuard::class, ['permission' => 'represtative_approval']],
                                ],
                                'actions' => [
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'successMetadata' => ['asd' => 'asd'],
                                'successJob' => [],
                                'failMetadata' => [],
                                'failJob' => [],
                            ],
                            'transtion2' => [
                                'from' => ['todo'],
                                'to' => ['in_progress', 'done'],
                                'guards' => [
                                    [TestDisallowedTransitionGuard::class, ['permission' => 'represtative_approval']],
                                ],
                                'actions' => [
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'successMetadata' => ['asd' => 'asd'],
                                'successJob' => [],
                                'failMetadata' => [],
                                'failJob' => [],
                            ],
                        ],
                    ],
                    'workflow2' => [],
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

    $name = 'name';

    /**
     * @var Stateable $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $this->assertEquals($modelInstance->name, $name);
    $this->assertEquals(Stateable::where('name', $name)->first()->name, $name);
});

it('can apply a workflow with initial state for a stateable model instance and retrieve', function () {

    $name = 'name1';
    $workflow = 'workflow1';
    $initalState = 'todo';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);

    $this->assertEquals($modelInstance->appliedWorkflow(), $workflow);
    $this->assertEquals($modelInstance->currentState(), $initalState);

});

it('can get a WorkflowNotFound', function () {

    $name = 'name3';
    $workflow = 'workflow_abc';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
})->expectException(WorkflowNotFoundException::class);

it('can get a WorkflowNotSupportedException', function () {

    $name = 'name3';
    $workflow = 'workflow2';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
})->expectException(WorkflowNotSupportedException::class);

it('can make a guard and get the result', function () {

    $guard1 = App::make(TestAllowedTransitionGuard::class);
    $guard2 = App::make(TestDisallowedTransitionGuard::class);

    $this->assertEquals($guard1->handle()->status, TransitionGuardResultDTO::ALLOWED);
    $this->assertEquals($guard2->handle()->status, TransitionGuardResultDTO::DISALLOWED);
});

it('can test', function () {

    $guard1 = App::make(TestAllowedTransitionGuard::class);
    $guard2 = App::make(TestDisallowedTransitionGuard::class);

    $this->assertEquals($guard1->handle()->status, TransitionGuardResultDTO::ALLOWED);
    $this->assertEquals($guard2->handle()->status, TransitionGuardResultDTO::DISALLOWED);
});

it('a', function () {

    $name = 'name4';
    $workflow = 'workflow1';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    //dd($modelInstance->canTransitionTo('in_progress')->allowed());
    // dd($modelInstance->definedTransitionStates());
    dd($modelInstance->allowedTransitionStates());

});

it('can create a stateable model instances', function () {

    $modelInstance = Stateable::create(
        ['name' => 'a']
    );

    /**
     * @var Model&StateableModelContract $modelInstance
     */
    $modelInstance->applyWorkflow('a');

    $modelInstance->canTransitionTo('b');

    $modelInstance->possibleTransitions();

    $modelInstance->transitionTo('b');

    $modelInstance->currentState();
});
