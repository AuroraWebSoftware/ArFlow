<?php

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
                        [\AuroraWebSoftware\ArFlow\Tests\Guards\TestAllowedTransitionGuard::class, ['permission' => 'represtative_approval']],
                    ],
                    'action' => [
                        [\AuroraWebSoftware\ArFlow\Tests\TransitionActions\TestSuccessTransitionAction::class, ['a' => 'b']],
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
