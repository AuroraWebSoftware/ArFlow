<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;

/**
 * Stateable Model
 */
interface StateableModelContract
{
    /**
     * workflow attribute for model class
     * @return string
     */
    public static function workflowAttribute(): string;

    /**
     * state attribute for model class (or type)
     * @return string
     */
    public static function stateAttribute(): string;

    /**
     * metadata attribute for model class (or type)
     * @return string
     */
    public static function stateMetadataAttribute(): string;

    /**
     * returns supported workflows of the model class (or type)
     * @return array<string>
     */
    public static function supportedWorkflows(): array;

    /**
     * applies the workflow to the instance
     *
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function applyWorkflow(string $workflow): bool;

    /**
     * Current model instance's applied workflow
     * @return string
     */
    public function appliedWorkflow(): string;

    /**
     * Current model instance's current workflow
     * @return string
     */
    public function currentState(): string;

    /**
     * Current metadata of the instance
     * @return array<string, mixed>
     */
    public function currentStateMetadata(): array;

    /**
     * check and return if
     * @param string $state
     * @param array<class-string>|null $withoutGuards
     * @return bool
     */
    public function canTransitionTo(string $state, array $withoutGuards = null): bool;

    /**
     * @param array<class-string>|null $withoutGuards
     * @return array|null
     */
    public function possibleTransitions(array $withoutGuards = null): ?array;

    /**
     * @param string $state
     * @param string|null $comment
     * @param ?class-string $byModelType
     * @param ?int $byModelId
     * @param array<string, mixed> $metadata
     * @param array<class-string> $withoutGuards
     * @param bool $transitionHistoryAction
     * @return bool
     */
    public function transitionTo(
        string  $state, ?string $comment = null,
        ?string $byModelType = null, ?int $byModelId = null,
        ?array  $metadata = null,
        ?array  $withoutGuards = null,
        bool    $transitionHistoryAction = true
    ): bool;

}