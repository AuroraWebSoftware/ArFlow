<?php

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionActionException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotAppliedException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use AuroraWebSoftware\ArFlow\Facades\ArFlow;
use AuroraWebSoftware\ArFlow\StateTransition;
use AuroraWebSoftware\ArFlow\Tests\Guards\TestAllowedTransitionGuard;
use AuroraWebSoftware\ArFlow\Tests\Guards\TestDisallowedTransitionGuard;
use AuroraWebSoftware\ArFlow\Tests\Jobs\TestTransitionSuccessJob;
use AuroraWebSoftware\ArFlow\Tests\Models\Stateable;
use AuroraWebSoftware\ArFlow\Tests\Models\User;
use AuroraWebSoftware\ArFlow\Tests\TransitionActions\TestFailTransitionAction;
use AuroraWebSoftware\ArFlow\Tests\TransitionActions\TestSuccessTransitionAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Artisan::call('migrate:fresh');

    //include_once __DIR__.'/../database/migrations/create_arflow_history_table.php';
    //(new create_arflow_history_table)->up();

    Config::set(
        [
            'arflow' => [
                'workflows' => [
                    'workflow1' => [
                        'states' => ['todo', 'in_progress', 'done', 'cancelled', 'in_review', 'on_going'],
                        'initial_state' => 'todo',
                        'transitions' => [
                            'transtion1' => [
                                'from' => ['todo'],
                                'to' => 'in_progress',
                                'guards' => [
                                    [TestAllowedTransitionGuard::class, ['permission' => 'approval']],
                                ],
                                'actions' => [
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'success_metadata' => ['asd' => 'asd'],
                                'success_jobs' => [
                                    [TestTransitionSuccessJob::class, ['aa' => 1]],
                                ],
                            ],
                            'transtion2' => [
                                'from' => ['todo'],
                                'to' => ['in_progress', 'done'],
                                'guards' => [
                                    [TestDisallowedTransitionGuard::class, ['permission' => 'approval']],
                                ],
                                'actions' => [
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'success_metadata' => ['asd' => 'asd'],
                                'success_jobs' => [],
                            ],
                            'transtion3' => [
                                'from' => ['todo'],
                                'to' => ['cancelled'],
                                'guards' => [
                                    [TestDisallowedTransitionGuard::class, ['permission' => 'represtative_approval']],
                                ],
                                'actions' => [
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'success_metadata' => ['asd' => 'asd'],
                                'success_jobs' => [],
                            ],
                            'transtion4' => [
                                'from' => ['todo'],
                                'to' => ['in_review'],
                                'guards' => [
                                    [TestAllowedTransitionGuard::class],
                                ],
                                'actions' => [
                                    [TestFailTransitionAction::class, ['a' => 'b']],
                                ],
                                'success_metadata' => ['asd' => 'asd'],
                                'success_jobs' => [],
                            ],
                            'transtion5' => [
                                'from' => ['in_progress'],
                                'to' => ['todo'],
                                'guards' => [
                                    [TestAllowedTransitionGuard::class],
                                ],
                                'actions' => [
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'success_metadata' => ['asd' => 'asd'],
                                'success_jobs' => [],
                            ],

                        ],
                    ],
                    'workflow2' => [],
                    'workflow3' => [
                        'states' => ['todo', 'in_progress', 'done', 'cancelled'],
                        'initial_state' => 'todo',
                    ],
                    'workflow4' => [
                        'states' => ['todo', 'in_progress', 'done'],
                        'initial_state' => 'todo',
                        'transitions' => [
                            'transtion1' => [
                                'from' => ['todo'],
                                'to' => 'in_progress',
                                'guards' => [],
                                'actions' => [],
                                'success_metadata' => ['asd' => 'asd'],
                                'success_jobs' => [
                                    [TestTransitionSuccessJob::class, ['aaa' => 1]],
                                ],
                            ],
                            'transtion2' => [
                                'from' => ['todo'],
                                'to' => ['done'],
                                'guards' => [
                                    [TestDisallowedTransitionGuard::class, ['permission' => 'represtative_approval']],
                                ],
                                'actions' => [
                                    [TestSuccessTransitionAction::class, ['a' => 'b']],
                                ],
                                'success_metadata' => ['asd' => 'asd'],
                                'success_jobs' => [],
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

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });

    $user = User::create();
    $this->actingAs($user);

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

    $this->assertEquals($modelInstance->currentWorkflow(), $workflow);
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
    $this->assertEquals($resultCollection->allowed(), true);

    $resultCollection->get('transtion1')
        ->each(fn (TransitionGuardResultDTO $item) => expect($item->allowed())->toBeTrue());

    $resultCollection->get('transtion2')
        ->each(fn (TransitionGuardResultDTO $item) => expect($item->allowed())->toBeFalse());
});

it('can throw WorkflowNotAppliedException on transitionGuardResults() without workflow application', function () {

    $name = 'name8';
    $toState = 'in_progress';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $resultCollection = $modelInstance->transitionGuardResults($toState);
})->expectException(WorkflowNotAppliedException::class);

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

})->expectException(TransitionNotFoundException::class);

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

it('can get all defined transition states', function () {
    $name = 'name8';
    $workflow = 'workflow1';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);

    expect($modelInstance->definedTransitionStates())
        ->toBeArray()
        ->toHaveCount(4)
        ->toContain('in_progress', 'done', 'cancelled', 'in_review');
});

it('can get all allowed transition states', function () {
    $name = 'name9';
    $workflow = 'workflow1';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);

    expect($modelInstance->allowedTransitionStates())
        ->toBeArray()
        ->toHaveCount(2)
        ->toContain('in_progress', 'in_review');
});

it('can transition to an allowed state', function () {
    Queue::fake();

    $name = 'name10';
    $workflow = 'workflow1';
    $toState = 'in_progress';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    $modelInstance->transitionTo($toState);

    expect($modelInstance->currentState())->toEqual($toState);
    Queue::assertPushed(TestTransitionSuccessJob::class);
});

it('can get TransitionActionException for a disallowed state', function () {

    Queue::fake();

    $name = 'name15';
    $workflow = 'workflow1';
    $toState = 'cancelled';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    $modelInstance->transitionTo($toState);

    Queue::assertNothingPushed();

})->expectException(TransitionActionException::class);

it('can get WorkflowNotFoundException for a disallowed state', function () {

    Queue::fake();

    $name = 'name15';
    $workflow = 'workflow_notfound';
    $toState = 'cancelled';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    $modelInstance->transitionTo($toState);

})->expectException(WorkflowNotFoundException::class);

it('can get all states of a workflow using Facade', function () {
    expect(ArFlow::getStates('workflow1'))
        ->toContain('todo', 'in_progress', 'done', 'cancelled', 'in_review', 'on_going')
        ->toHaveCount(6);
});

it('can transition to an non-guarded and non actioned state', function () {
    Queue::fake();

    $name = 'name100';
    $workflow = 'workflow4';
    $toState = 'in_progress';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    $modelInstance->transitionTo($toState);

    expect($modelInstance->currentState())->toEqual($toState);
    Queue::assertPushed(TestTransitionSuccessJob::class);
});

it('can get transitionGuardResults_', function () {

    Queue::fake();

    $name = 'name8';
    $workflow = 'workflow1';
    $toState = 'in_progress';
    $toState2 = 'todo';

    /**
     * @var StateableModelContract & Model $modelInstance
     */
    $modelInstance = Stateable::create(
        ['name' => $name]
    );

    $modelInstance->applyWorkflow($workflow);
    $modelInstance->transitionTo($toState);
    $resultCollection = $modelInstance->transitionGuardResults($toState);

    $modelInstance->transitionTo($toState2);
    $resultCollection = $modelInstance->transitionGuardResults($toState2);

        $lastDate = StateTransition::where([
            'workflow' => 'workflow1',
            'model_type' => Stateable::class,
            'model_id' => $modelInstance->id
        ])->orderBy('id', 'desc')->first()->updated_at->format('Y-m-d H:i:s');

    expect( $modelInstance->lastUpdatedTime()->format('Y-m-d H:i:s'))->toBe($lastDate);
    Queue::assertPushed(TestTransitionSuccessJob::class);

});
