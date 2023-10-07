<?php

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionNotFoundException;
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
use function PHPUnit\Framework\assertEquals;

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
                        'states' => ['todo', 'in_progress', 'done', 'cancelled'],
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
                    'workflow3' => [
                        'states' => ['todo', 'in_progress', 'done', 'cancelled'],
                        'initial_state' => 'todo',
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

it('can get transitionGuardResults', function () {

    $name = 'name7';
    $workflow = 'workflow1';
    $toState = 'in_progress';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    $resultCollection = $modelInstance->transitionGuardResults($toState);
    $this->assertEquals($resultCollection->count(), 2);
    $this->assertEquals($resultCollection->allowed(), TransitionGuardResultDTO::ALLOWED);

    $resultCollection->get('transtion1')
        ->each(fn(TransitionGuardResultDTO $item) => assertEquals($item->allowed(), true));

    $resultCollection->get('transtion2')
        ->each(fn(TransitionGuardResultDTO $item) => assertEquals($item->allowed(), false));
});


it('can throw WorkflowNotFoundException on transitionGuardResults() without workflow application', function () {

    $name = 'name8';
    $toState = 'in_progress';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $resultCollection = $modelInstance->transitionGuardResults($toState);
})->expectException(\AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotAppliedException::class);

it('can throw TransitionNotFoundException on transitionGuardResults() if no transition found', function () {

    $name = 'name9';
    $workflow = 'workflow3';
    $toState = 'cancelled';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    $resultCollection = $modelInstance->transitionGuardResults($toState);

    dd($resultCollection);

})->expectException(\AuroraWebSoftware\ArFlow\Exceptions\TransitionNotFoundException::class);


it('can check transitionTo States', function () {
    $name = 'name8';
    $workflow = 'workflow1';
    $toState1 = 'in_progress';
    $toState2 = 'cancelled';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);

    $this->assertTrue($modelInstance->canTransitionTo($toState1));
    $this->assertFalse($modelInstance->canTransitionTo($toState2));
});

it('can get all defined transitions', function () {
    $name = 'name8';
    $workflow = 'workflow1';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);

    dd($modelInstance->definedTransitionStates());
});


