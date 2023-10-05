<?php

namespace AuroraWebSoftware\ArFlow\DTOs;

use Illuminate\Support\Collection;

class TransitionGuardReturnDTO
{
    const ALLOWED = 1;

    const DISALLOWED = 2;

    public static function build(int $status): self
    {
        return new self($status);
    }

    public function __construct(
        public int $status,
        public ?Collection $messages = null,
    ) {
    }

    public function addMessage(string $message): TransitionGuardReturnDTO
    {
        $this->messages->push($message);

        return $this;
    }

    public function allowed(): bool
    {
        return $this->status == self::ALLOWED;
    }

    /**
     * @return Collection<string>
     */
    public function messages(): Collection
    {
        return $this->messages;
    }
}
