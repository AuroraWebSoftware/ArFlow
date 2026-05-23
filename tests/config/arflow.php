<?php

use AuroraWebSoftware\ArFlow\Tests\Guards\TestAllowedTransitionGuard;
use AuroraWebSoftware\ArFlow\Tests\TransitionActions\TestSuccessTransitionAction;

return [
    'workflows' => [
        'workflow1' => [
            'states' => ['a', 'b'],
            'initial_state' => 'a',
            'transitions' => [
                'transtion1' => [
                    'from' => ['a'],
                    'to' => 'b',
                    'guard' => [
                        [TestAllowedTransitionGuard::class, ['permission' => 'represtative_approval']],
                    ],
                    'action' => [
                        [TestSuccessTransitionAction::class, ['a' => 'b']],
                    ],
                    'successMetadata' => ['asd' => 'asd'],
                    'successJob' => [],
                    'failMetadata' => [],
                    'failJob' => [],
                ],
            ],

        ],
    ],
];
