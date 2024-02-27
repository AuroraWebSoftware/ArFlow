<?php

namespace AuroraWebSoftware\ArFlow\Traits;

use AuroraWebSoftware\AAuth\Facades\AAuth;
use AuroraWebSoftware\ArFlow\Collections\TransitionGuardResultCollection;
use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionActionContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionGuardContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\InitialStateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\StateMetadataNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\StateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionActionException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotAppliedException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use AuroraWebSoftware\ArFlow\StateTransition;
use AuroraWebSoftware\ArFlow\TransitionActions\LogHistoryTransitionAction;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

trait HasState
{
    public function getId(): int|string
    {
        return $this->getAttribute('id');
    }

    /**
     * returns all workflows
     *
     * @return array<string>
     */
    private function getConfigWorkflows(): array
    {
        $workflows = [];

        foreach (Config::get('arflow.workflows') ?? [] as $key => $value) {
            $workflows[] = $key;
        }

        return $workflows;
    }

    /**
     * @throws InitialStateNotFoundException
     */
    private function getInitialState(string $workflow): string
    {
        $workflows = [];

        foreach (Config::get('arflow.workflows') ?? [] as $key => $value) {
            if ($key == $workflow) {
                return (string) $value['initial_state'];
            }
        }

        throw new InitialStateNotFoundException();
    }

    public function getGuarded(): array
    {
        $self = self::class;

        return [$self::workflowAttribute(), $self::stateAttribute(), $self::stateMetadataAttribute()];
    }

    /**
     * workflow attribute of the model on the db
     */
    public static function workflowAttribute(): string
    {
        return 'workflow';
    }

    /**
     * state attribute of the model on the db
     */
    public static function stateAttribute(): string
    {
        return 'state';
    }

    /**
     * state metadata attribute of the model on the db
     */
    public static function stateMetadataAttribute(): string
    {
        return 'state_metadata';
    }

    /**
     * applies workflow to the model instance
     *
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     * @throws TransitionActionException
     * @throws InitialStateNotFoundException
     */
    public function applyWorkflow(string $workflow): bool
    {
        /**
         * @var Model&StateableModelContract $self
         * @var Model&StateableModelContract $this
         */
        $self = self::class;

        if (! in_array($workflow, $this->getConfigWorkflows())) {
            throw new WorkflowNotFoundException("$workflow Not Found");
        }

        if (! in_array($workflow, $self::supportedWorkflows())) {
            throw new WorkflowNotSupportedException("$workflow Not Supported by $self");
        }

        $this->{$self::workflowAttribute()} = $workflow;
        $this->{$self::stateAttribute()} = $this->getInitialState($workflow);

        $this->save();

        $historyAction = App::make(LogHistoryTransitionAction::class);
        $historyAction->boot($this, '', $this->getInitialState($workflow),
            [
                'actor_model_type' => null,
                'actor_model_id' => null,
            ]
        );
        $historyAction->handle();

        return true;

    }

    /**
     * @throws WorkflowNotAppliedException
     */
    public function currentWorkflow(): string
    {
        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        $attribute = $this->getAttribute($self::workflowAttribute());
        if (! $attribute) {
            throw new WorkflowNotAppliedException;
        }

        return $attribute;
    }

    /**
     * returns current state of the model instance
     *
     * @throws StateNotFoundException
     */
    public function currentState(): string
    {
        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        $attribute = $this->getAttribute($self::stateAttribute());
        if (! $attribute) {
            throw new StateNotFoundException();
        }

        return $attribute;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws StateMetadataNotFoundException
     */
    public function currentStateMetadata(): array
    {
        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        $attribute = $this->getAttribute($self::stateMetadataAttribute());
        if (! $attribute) {
            throw new StateMetadataNotFoundException();
        }

        return $this->getAttribute($self::stateMetadataAttribute());
    }

    /**
     * @return TransitionGuardResultCollection<string, Collection<TransitionGuardResultDTO>>
     *
     * @throws WorkflowNotFoundException
     * @throws TransitionNotFoundException
     * @throws StateNotFoundException
     * @throws WorkflowNotSupportedException
     * @throws WorkflowNotAppliedException
     */
    public function transitionGuardResults(string $toState, ?array $withoutGuards = null): TransitionGuardResultCollection
    {
        $resultCollection = TransitionGuardResultCollection::make();

        $workflowValues = Config::get('arflow.workflows')[$this->currentWorkflow()] ?? null;

        if (! $workflowValues) {
            throw new WorkflowNotFoundException($this->currentWorkflow().' Not Found');
        }

        $transitionValues = $workflowValues['transitions'] ?? null;

        if (! $transitionValues) {
            throw new TransitionNotFoundException;
        }

        foreach ($transitionValues as $transitionKey => $transitionValue) {
            $fromStateValues = is_array($transitionValue['from']) ? $transitionValue['from'] : [$transitionValue['from']];
            $toStateValues = is_array($transitionValue['to']) ? $transitionValue['to'] : [$transitionValue['to']];
            $guardValues = $transitionValue['guards'];

            if (in_array($this->currentState(), $fromStateValues) and in_array($toState, $toStateValues)) {
                $handledGuardInstancesResults = collect();
                foreach ($guardValues as $guardValue) {
                    /**
                     * @var TransitionGuardContract $guardInstance
                     */
                    $guardInstance = App::make($guardValue[0]);
                    $guardInstance->boot($this, $this->currentState(), $toState, $guardValue[1] ?? []);
                    $handledGuardInstancesResults->push($guardInstance->handle());
                }

                $resultCollection->put($transitionKey, $handledGuardInstancesResults);
            }
        }

        return $resultCollection;
    }

    /**
     * check if state can transition to a state
     *
     * @param  ?array  $withoutGuards
     *
     * @throws StateNotFoundException
     * @throws TransitionNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function canTransitionTo(string $toState, ?array $withoutGuards = null): bool
    {
        return $this->transitionGuardResults($toState, $withoutGuards)->allowed();
    }

    public function definedTransitionKeys(?array $withoutGuards = null): ?array
    {
        // TODO: Implement definedTransitionKeys() method.
        // testleri yazılacak
    }

    public function allowedTransitionKeys(?array $withoutGuards = null): ?array
    {
        // TODO: Implement allowedTransitionKeys() method.
        // testleri yazılacak
    }

    /**
     * @throws StateNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws TransitionNotFoundException
     */
    public function definedTransitionStates(?array $withoutGuards = null): ?array
    {
        $definedTransitionStates = [];

        $workflowValues = Config::get('arflow.workflows')[$this->currentWorkflow()] ?? null;

        if (! $workflowValues) {
            throw new WorkflowNotFoundException($this->currentWorkflow().' Not Found');
        }

        $transitionValues = $workflowValues['transitions'] ?? null;
        if (! $transitionValues) {
            throw new TransitionNotFoundException;
        }

        foreach ($transitionValues as $transitionKey => $transitionValue) {
            $fromStateValues = is_array($transitionValue['from']) ? $transitionValue['from'] : [$transitionValue['from']];

            if (in_array($this->currentState(), $fromStateValues)) {
                $toStateValues = is_array($transitionValue['to']) ? $transitionValue['to'] : [$transitionValue['to']];
                $definedTransitionStates = array_merge($definedTransitionStates, $toStateValues);
            }
        }

        return array_unique($definedTransitionStates);
    }

    /**
     * @throws StateNotFoundException
     * @throws TransitionNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function allowedTransitionStates(?array $withoutGuards = null): ?array
    {
        $allowedTransitionStates = [];
        $definedTransitionStates = $this->definedTransitionStates($withoutGuards);

        foreach ($definedTransitionStates as $definedTransitionState) {
            if ($this->canTransitionTo($definedTransitionState)) {
                $allowedTransitionStates[] = $definedTransitionState;
            }
        }

        return $allowedTransitionStates;
    }

    /**
     * @throws WorkflowNotAppliedException
     */
    public function lastUpdatedTime(): ?DateTime
    {
        return StateTransition::where([
            'workflow' => $this->currentWorkflow(),
            'model_type' => self::class,
            'model_id' => $this->id,
        ])->orderBy('id', 'desc')?->first()?->updated_at;
    }

    /**
     * @param  class-string|null  $actorModelType
     * @param  array<class-string>|null  $withoutGuards
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
    ): bool {

        if (! $this->canTransitionTo($toState, $withoutGuards)) {
            throw new TransitionActionException();
        }

        $workflowValues = Config::get('arflow.workflows')[$this->currentWorkflow()] ?? [];
        if (! $workflowValues) {
            throw new WorkflowNotFoundException($this->currentWorkflow().' Not Found');
        }

        $transitionValues = $workflowValues['transitions'] ?? null;
        if (! $transitionValues) {
            throw new TransitionNotFoundException;
        }

        $transitionKeyItem = '';
        $actions = '';
        $transitionFound = false;
        foreach ($transitionValues as $transitionKeyItem => $transition) {

            if ($transitionKey != null and $transitionKeyItem != $transitionKey) {
                continue;
            }

            $from = is_array($transition['from']) ? $transition['from'] : [$transition['from']];
            $to = is_array($transition['to']) ? $transition['to'] : [$transition['to']];
            $actions = $transition['actions'];
            $successJobs = $transition['success_jobs'];

            $actionInstances = [];

            if ($logHistoryTransitionAction) {
                $actorModelId = $actorModelId ?: Auth::id();
                $actorModelType = $actorModelType ?: get_class(Auth::user());

                $actions[] = [
                    LogHistoryTransitionAction::class,
                    [
                        'actor_model_type' => $actorModelType,
                        'actor_model_id' => $actorModelId,
                        'comment' => $comment,
                        'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                    ],
                ];
            }

            if (in_array($this->currentState(), $from) and in_array($toState, $to)) {

                foreach ($actions as $action) {
                    /**
                     * @var array<TransitionActionContract> $actionInstances
                     */
                    $actionInstances[$action[0]] = App::make($action[0]);
                    $actionInstances[$action[0]]->boot($this, $this->currentState(), $toState, $action[1] ?? []);

                    try {
                        $actionInstances[$action[0]]->handle();
                    } catch (\Exception $e) {
                        foreach ($actionInstances as $actionInstance) {
                            $actionInstance->failed();
                        }
                        throw new TransitionActionException('Transition Action Failed: '.$e->getMessage());
                    }
                }
                foreach ($successJobs as $successJob) {

                    $userId = null;
                    if (Auth::check() && isset($metadata['userId']) && isset($metadata['roleId'])) {
                        $userId = $metadata['userId'];
                        if (isset($successJob[1]) && is_array($successJob[1])) {
                            $successJobParameter = array_merge($successJob[1], ['userId' => $metadata['userId'],'roleId' => $metadata['roleId']]);
                        } else {
                            $successJobParameter =['userId' => $metadata['userId'],'roleId' => $metadata['roleId']];
                        }
                    }
                    dispatch(new $successJob[0]($this, $this->currentState(), $toState, $successJobParameter ?? []));
                }

                $transitionFound = true;
                break;
            }
        }

        if (! $transitionFound) {
            throw new TransitionActionException('Transition Not Found');
        }

        $self = self::class;
        $this->{$self::stateAttribute()} = $toState;
        $this->{$self::stateMetadataAttribute()} = [
            'latest_from_state' => $this->currentState(),
            'latest_transition_key' => $transitionKeyItem,
            'latest_actions' => $actions,
        ];

        return $this->save();
    }
}
