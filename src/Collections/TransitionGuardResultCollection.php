<?php

namespace AuroraWebSoftware\ArFlow\Collections;

use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use Illuminate\Support\Collection;

/**
 * @extends Collection<int, TransitionGuardResultDTO>
 */
class TransitionGuardResultCollection extends Collection
{
    public function allMessages(): array
    {
        $allMessages = [];

        $this->each(
            function (TransitionGuardResultDTO $transitionGuardResultDTO) use ($allMessages) {
                $allMessages = array_merge($allMessages, $transitionGuardResultDTO->messages());
            }
        );

        return $allMessages;
    }
}