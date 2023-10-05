<?php

it('can create a stateable model instance', function () {

    $modelInstance = \AuroraWebSoftware\ArFlow\Tests\Models\StateableModel::create(
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
