<?php

namespace AuroraWebSoftware\ArFlow\Collections;

use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use Illuminate\Support\Collection;

/**
 * @extends Collection<string, TransitionGuardResultCollection<TransitionGuardResultDTO>>
 */
class TransitionGuardResultCollection extends Collection
{
    const ALLOWED = 1;
    const DISALLOWED = 2;

    public function allowed(): bool
    {


    }

    /**
     * @return array<string, array<string>>
     */
    public function messages(): array
    {
        $allMessages = [];

        $this->each(
            function (Collection $collection, $key1) use (&$allMessages) {

                $collection->each(
                    function (TransitionGuardResultDTO $transitionGuardResultDTO) use (&$allMessages, $key1) {
                        $allMessages[$key1] = array_merge($allMessages[$key1] ?? [], $transitionGuardResultDTO->messages());
                    }
                );
            }
        );

        return $allMessages;
    }
}