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
        foreach ($this as $collection) {

            /**
             * @var Collection<int, TransitionGuardResultDTO> $collection
             */
            $allowed = true;
            foreach ($collection as $transitionGuardResultDTO) {

                /**
                 * @var TransitionGuardResultDTO $transitionGuardResultDTO
                 */
                if ($transitionGuardResultDTO->status == TransitionGuardResultDTO::DISALLOWED) {
                    $allowed = false;
                }
            }

            if ($allowed) {
                return true;
            }
        }

        return false;
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
