<?php

namespace AuroraWebSoftware\ArFlow\Contacts;

use AuroraWebSoftware\ArFlow\Collections\TransitionGuardResultCollection;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\StateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionActionException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotAppliedException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use DateTime;
use Illuminate\Support\Collection;
use Throwable;

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

    public function getId(): int|string;

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
    public function currentWorkflow(): string;

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
     * @param  array<class-string>|null  $withoutGuards
     * @return TransitionGuardResultCollection<string, Collection<int, TransitionGuardResultDTO>>
     *
     * @throws WorkflowNotFoundException
     */
    public function transitionGuardResults(string $toState, ?array $withoutGuards = null): TransitionGuardResultCollection;

    /**
     * @param  array<class-string>|null  $withoutGuards
     */
    public function canTransitionTo(string $toState, ?array $withoutGuards = null): bool;

    /**
     * @param  array<class-string>|null  $withoutGuards
     * @return array<string>|null
     */
    public function definedTransitionKeys(?array $withoutGuards = null): ?array;

    /**
     * @param  array<class-string>|null  $withoutGuards
     * @return array<string>|null
     *
     * @throws WorkflowNotFoundException
     * @throws Throwable
     */
    public function allowedTransitionKeys(?array $withoutGuards = null): ?array;

    /**
     * @param  array<class-string>|null  $withoutGuards
     * @return array<string>|null
     */
    public function definedTransitionStates(?array $withoutGuards = null): ?array;

    /**
     * @param  array<class-string>|null  $withoutGuards
     * @return array<string>|null
     *
     * @throws WorkflowNotFoundException
     * @throws Throwable
     */
    public function allowedTransitionStates(?array $withoutGuards = null): ?array;

    public function lastUpdatedTime(): ?DateTime;

    /**
     * @param  class-string|null  $actorModelType
     * @param  array<class-string>|null  $withoutGuards
     * @param  array<string, mixed>|null  $metadata
     *
     * @throws StateNotFoundException
     * @throws TransitionActionException
     * @throws TransitionNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function transitionTo(
        string $toState, ?string $comment = null,
        ?string $actorModelType = null, ?int $actorModelId = null,
        ?array $metadata = null,
        ?array $withoutGuards = null,
        ?string $transitionKey = null,
        bool $logHistoryTransitionAction = true
    ): bool;
}
