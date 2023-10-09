<?php

namespace AuroraWebSoftware\ArFlow\Traits;

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
use AuroraWebSoftware\ArFlow\TransitionActions\LogHistoryTransitionAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Throwable;

trait HasState
{
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
                return (string)$value['initial_state'];
            }
        }

        throw new InitialStateNotFoundException();
    }

    public function getGuarded(): array
    {
        $self = self::class;

        return [$self::workflowAttribute(), $self::stateAttribute(), $self::stateMetadataAttribute()];
    }

    /*
     * servis mantığına şuan için ihtiyaç yok
    public function bootArflow(): void
    {
        $this->service = new ArFlow($this);
    }
    */

    /**
     * workflow attribute of the model on the db
     * @return string
     */
    public static function workflowAttribute(): string
    {
        return 'workflow';
    }

    /**
     * state attribute of the model on the db
     * @return string
     */
    public static function stateAttribute(): string
    {
        return 'state';
    }

    /**
     * state metadata attribute of the model on the db
     * @return string
     */
    public static function stateMetadataAttribute(): string
    {
        return 'state_metadata';
    }

    /**
     * applies workflow to the model instance
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     * @throws Throwable
     */
    public function applyWorkflow(string $workflow): bool
    {
        /**
         * @var Model&StateableModelContract $self
         * @var Model&StateableModelContract $this
         */
        $self = self::class;

        throw_unless(in_array($workflow, $this->getConfigWorkflows()), WorkflowNotFoundException::class, "$workflow Not Found");
        throw_unless(in_array($workflow, $self::supportedWorkflows()), WorkflowNotSupportedException::class, "$workflow Not Supported by $self");

        $this->{$self::workflowAttribute()} = $workflow;
        $this->{$self::stateAttribute()} = $this->getInitialState($workflow);

        // todo histry action eklenecek. (initial state için)

        return $this->save();
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
        if (!$attribute) {
            throw new WorkflowNotAppliedException;
        }
        return $attribute;
    }

    /**
     * returns current state of the model instance
     * @return string
     * @throws StateNotFoundException
     */
    public function currentState(): string
    {
        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        $attribute = $this->getAttribute($self::stateAttribute());
        if (!$attribute) {
            throw new StateNotFoundException();
        }
        return $attribute;
    }

    /**
     * @return array<string, mixed>
     * @throws StateMetadataNotFoundException
     */
    public function currentStateMetadata(): array
    {
        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        $attribute = $this->getAttribute($self::stateMetadataAttribute());
        if (!$attribute) {
            throw new StateMetadataNotFoundException();
        }
        return $this->getAttribute($self::stateMetadataAttribute());
    }

    /**
     * @return TransitionGuardResultCollection<string, Collection<TransitionGuardResultDTO>>
     * @throws WorkflowNotFoundException
     * @throws TransitionNotFoundException
     * @throws StateNotFoundException
     * @throws WorkflowNotSupportedException
     * @throws WorkflowNotAppliedException
     */
    public function transitionGuardResults(string $toState, array $withoutGuards = null): TransitionGuardResultCollection
    {
        $resultCollection = TransitionGuardResultCollection::make();

        $workflowValues = Config::get('arflow.workflows')[$this->currentWorkflow()] ?? null;

        if (!$workflowValues) {
            throw new WorkflowNotFoundException($this->currentWorkflow() . ' Not Found');
        }

        $transitionValues = $workflowValues['transitions'] ?? null;

        if (!$transitionValues) {
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
     * @param string $toState
     * @param ?array $withoutGuards
     * @return bool
     * @throws StateNotFoundException
     * @throws TransitionNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function canTransitionTo(string $toState, array $withoutGuards = null): bool
    {
        return $this->transitionGuardResults($toState, $withoutGuards)->allowed();
    }

    public function definedTransitionKeys(array $withoutGuards = null): ?array
    {
        // TODO: Implement definedTransitionKeys() method.
        // testleri yazılacak
    }

    public function allowedTransitionKeys(array $withoutGuards = null): ?array
    {
        // TODO: Implement allowedTransitionKeys() method.
        // testleri yazılacak
    }

    /**
     * @param array|null $withoutGuards
     * @return array|null
     * @throws StateNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws TransitionNotFoundException
     */
    public function definedTransitionStates(array $withoutGuards = null): ?array
    {
        $definedTransitionStates = [];

        $workflowValues = Config::get('arflow.workflows')[$this->currentWorkflow()] ?? null;

        if (!$workflowValues) {
            throw new WorkflowNotFoundException($this->currentWorkflow() . ' Not Found');
        }

        $transitionValues = $workflowValues['transitions'] ?? null;
        if (!$transitionValues) {
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
     * @param array|null $withoutGuards
     * @return array|null
     * @throws StateNotFoundException
     * @throws TransitionNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function allowedTransitionStates(array $withoutGuards = null): ?array
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
     * @param string $toState
     * @param string|null $comment
     * @param string|null $byModelType
     * @param int|null $byModelId
     * @param array|null $metadata
     * @param array|null $withoutGuards
     * @param string|null $transitionKey
     * @param bool $logHistoryTransitionAction
     * @return bool
     * @throws StateNotFoundException
     * @throws TransitionActionException
     * @throws TransitionNotFoundException
     * @throws WorkflowNotAppliedException
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function transitionTo(
        string $toState, string $comment = null,
        string $byModelType = null, int $byModelId = null,
        array  $metadata = null,
        array  $withoutGuards = null,
        string $transitionKey = null,
        bool   $logHistoryTransitionAction = true
    ): bool
    {
        // todo tekrar düşünülmeli

        if (!$this->canTransitionTo($toState, $withoutGuards)) {
            throw new TransitionActionException();
        }

        $workflowValues = Config::get('arflow.workflows')[$this->currentWorkflow()] ?? [];
        if (!$workflowValues) {
            throw new WorkflowNotFoundException($this->currentWorkflow() . ' Not Found');
        }

        $transitionValues = $workflowValues['transitions'] ?? null;
        if (!$transitionValues) {
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
                $actions[] = [LogHistoryTransitionAction::class];
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
                        throw new TransitionActionException("Transition Action Failed: " . $e->getMessage());
                    }
                }

                foreach ($successJobs as $successJob) {
                    $successJob::dispatch();
                }

                $transitionFound = true;
                break;
            }
        }

        if (!$transitionFound) {
            throw new TransitionActionException("Transition Not Found");
        }


        //throw new WorkflowNotSupportedException();
        //throw new WorkflowNotFoundException();

        $self = self::class;
        $this->{$self::stateAttribute()} = $toState;
        $this->{$self::stateMetadataAttribute()} = [
            'latest_from_state' => $this->currentState(),
            'latest_transition_key' => $transitionKeyItem,
            'latest_actions' => $actions,
        ];

        $this->save();

        // job'lar

        return true;
    }
}
