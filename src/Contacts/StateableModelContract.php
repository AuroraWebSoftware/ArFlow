<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\Collections\TransitionGuardResultCollection;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use Illuminate\Support\Collection;

/**
 * Stateable Model
 */
interface StateableModelContract
{
    /**
     * workflow attribute for model class
     */
    public static function workflowAttribute(): string;

    /**
     * state attribute for model class (or type)
     */
    public static function stateAttribute(): string;

    /**
     * metadata attribute for model class (or type)
     */
    public static function stateMetadataAttribute(): string;

    /**
     * returns supported workflows of the model class (or type)
     *
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
     */
    public function appliedWorkflow(): string;

    /**
     * Current model instance's current workflow
     */
    public function currentState(): string;

    /**
     * Current metadata of the instance
     *
     * @return array<string, mixed>
     */
    public function currentStateMetadata(): array;

    /**
     * @return TransitionGuardResultCollection<string, Collection<int, TransitionGuardResultDTO>>
     *
     * @throws WorkflowNotFoundException
     */
    public function transitionGuardResults(string $toState, array $withoutGuards = null): TransitionGuardResultCollection;

    public function canTransitionTo(string $toState, array $withoutGuards = null): bool;

    /**
     * @param  array<class-string>|null  $withoutGuards
     */
    public function allowedTransitions(array $withoutGuards = null): ?array;

    /**
     * @param  ?class-string  $byModelType
     * @param  ?int  $byModelId
     * @param  array<string, mixed>  $metadata
     * @param  array<class-string>  $withoutGuards
     */
    public function transitionTo(
        string $state, string $comment = null,
        string $byModelType = null, int $byModelId = null,
        array $metadata = null,
        array $withoutGuards = null,
        bool $transitionHistoryAction = true
    ): bool;
}
