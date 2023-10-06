<?php

namespace AuroraWebSoftware\ArFlow\DTOs;

class TransitionActionResultDTO
{
    const SUCCESS = 1;
    const FAIL = 2;

    public static function build(int $status): self
    {
        return new self($status);
    }

    /**
     * @param int $status
     * @param array<string>|null $messages
     */
    public function __construct(
        public int    $status,
        public ?array $messages = [],
    )
    {
    }

    /**
     * @param string $message
     * @return TransitionActionResultDTO
     */
    public function addMessage(string $message): TransitionActionResultDTO
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * @return bool
     */
    public function executed(): bool
    {
        return ($this->status) == self::SUCCESS;
    }

    /**
     * @return array<string>
     */
    public function messages(): array
    {
        return $this->messages;
    }

}