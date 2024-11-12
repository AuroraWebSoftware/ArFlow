<?php

namespace AuroraWebSoftware\ArFlow\DTOs;

class TransitionGuardResultDTO
{
    const ALLOWED = 1;

    const DISALLOWED = 2;

    public static function build(int $status): self
    {
        return new self($status);
    }

    /**
     * @param  array<string>|null  $messages
     */
    public function __construct(
        public int $status,
        public ?array $messages = [],
    ) {}

    public function addMessage(string $message): TransitionGuardResultDTO
    {
        $this->messages[] = $message;

        return $this;
    }

    public function allowed(): bool
    {
        return $this->status == self::ALLOWED;
    }

    /**
     * @return array<string>
     */
    public function messages(): array
    {
        return $this->messages;
    }
}
