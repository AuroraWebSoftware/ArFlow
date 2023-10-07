<?php

namespace AuroraWebSoftware\ArFlow\Traits;

use AuroraWebSoftware\ArFlow\Collections\TransitionGuardResultCollection;
use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionActionContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionGuardContract;
use AuroraWebSoftware\ArFlow\DTOs\TransitionGuardResultDTO;
use AuroraWebSoftware\ArFlow\Exceptions\InitialStateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionActionException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotAppliedException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
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
    public function bootArflow(): void
    {
        $this->service = new ArFlow($this);
    }
    */

    public static function workflowAttribute(): string
    {
        return 'workflow';
    }

    public static function stateAttribute(): string
    {
        return 'state';
    }

    public static function stateMetadataAttribute(): string
    {
        return 'state_metadata';
    }

    /**
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     * @throws Throwable
     */
    public function applyWorkflow(string $workflow): bool
    {
        // histroy action eklenecek.
        /**
         * @var Model&StateableModelContract $self
         * @var Model&StateableModelContract $this
         */
        $self = self::class;

        throw_unless(in_array($workflow, $this->getConfigWorkflows()), WorkflowNotFoundException::class, "$workflow Not Found");
        throw_unless(in_array($workflow, $self::supportedWorkflows()), WorkflowNotSupportedException::class, "$workflow Not Supported by $self");

        $this->{$self::workflowAttribute()} = $workflow;
        $this->{$self::stateAttribute()} = $this->getInitialState($workflow);

        return $this->save();
    }

    /**
     * @throws Throwable
     */
    public function appliedWorkflow(): string
    {

        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */

        $attribute = $this->getAttribute($self::workflowAttribute());
        throw_unless($attribute, WorkflowNotAppliedException::class);
        return $attribute;
    }

    public function currentState(): string
    {
        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        return $this->getAttribute($self::stateAttribute());
    }

    public function currentStateMetadata(): array
    {
        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        return $this->getAttribute($self::stateMetadataAttribute());
    }

    /**
     * @return TransitionGuardResultCollection<string, Collection<TransitionGuardResultDTO>>
     *
     * @throws WorkflowNotFoundException|Throwable
     */
    public function transitionGuardResults(string $toState, array $withoutGuards = null): TransitionGuardResultCollection
    {
        $collection = TransitionGuardResultCollection::make();

        $appliedWorkflowValue = Config::get('arflow.workflows')[$this->appliedWorkflow()];
        throw_unless($appliedWorkflowValue, WorkflowNotFoundException::class, $this->appliedWorkflow() . ' Not Found');

        $transitions = $appliedWorkflowValue['transitions'] ?? null;

        throw_unless($transitions, TransitionNotFoundException::class);

        foreach ($transitions as $transitionKey => $transition) {
            $from = is_array($transition['from']) ? $transition['from'] : [$transition['from']];
            $to = is_array($transition['to']) ? $transition['to'] : [$transition['to']];
            $guards = $transition['guards'];

            if (in_array($this->currentState(), $from) and in_array($toState, $to)) {

                $c = collect();
                foreach ($guards as $guardClass) {
                    /**
                     * @var TransitionGuardContract $guardInstance
                     */
                    $guardInstance = App::make($guardClass[0]);
                    $guardInstance->boot($this, $this->currentState(), $toState, $guardClass[1] ?? []);
                    $c->push($guardInstance->handle());
                }

                $collection->put($transitionKey, $c);
            }
        }

        return $collection;
    }

    /**
     * @throws Throwable
     * @throws WorkflowNotFoundException
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

    public function definedTransitionStates(array $withoutGuards = null): ?array
    {
        $definedTransitions = [];

        $appliedWorkflowValue = Config::get('arflow.workflows')[$this->appliedWorkflow()];
        throw_unless($appliedWorkflowValue, WorkflowNotFoundException::class, $this->appliedWorkflow() . ' Not Found');

        $transitions = $appliedWorkflowValue['transitions'] ?? null;
        throw_unless($transitions, TransitionNotFoundException::class);

        foreach ($transitions as $transitionKey => $transition) {
            $from = is_array($transition['from']) ? $transition['from'] : [$transition['from']];

            if (in_array($this->currentState(), $from)) {
                $to = is_array($transition['to']) ? $transition['to'] : [$transition['to']];
                $definedTransitions = array_merge($definedTransitions, $to);
            }
        }

        return array_unique($definedTransitions);
    }

    /**
     * @throws Throwable
     * @throws WorkflowNotFoundException
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
     * @param bool $transitionHistoryAction
     * @return bool
     * @throws Throwable
     * @throws TransitionActionException
     * @throws WorkflowNotFoundException
     * @throws WorkflowNotSupportedException
     */
    public function transitionTo(
        string $toState, string $comment = null,
        string $byModelType = null, int $byModelId = null,
        array  $metadata = null,
        array  $withoutGuards = null,
        string $transitionKey = null,
        bool   $transitionHistoryAction = true
    ): bool
    {
        // todo tekrar düşünülmeli
        try {
            if (!$this->canTransitionTo($toState, $withoutGuards)) {
                return false;
            }
        } catch (Throwable $e) {
            throw new TransitionActionException($e->getMessage());
        }

        $appliedWorkflowValue = Config::get('arflow.workflows')[$this->appliedWorkflow()];
        throw_unless($appliedWorkflowValue, WorkflowNotFoundException::class, $this->appliedWorkflow() . ' Not Found');

        $transitions = $appliedWorkflowValue['transitions'] ?? null;

        throw_unless($transitions, TransitionNotFoundException::class);

        $transitionKeyItem = '';
        $actions = '';
        $transitionFound = false;
        foreach ($transitions as $transitionKeyItem => $transition) {

            if ($transitionKey != null and $transitionKeyItem != $transitionKey) {
                continue;
            }

            $from = is_array($transition['from']) ? $transition['from'] : [$transition['from']];
            $to = is_array($transition['to']) ? $transition['to'] : [$transition['to']];
            $actions = $transition['actions'];

            if (in_array($this->currentState(), $from) and in_array($toState, $to)) {
                foreach ($actions as $actionClass) {
                    /**
                     * @var TransitionActionContract $actionInstance
                     */
                    $actionInstance = App::make($actionClass[0]);
                    $actionInstance->boot($this, $this->currentState(), $toState, $actionClass[1] ?? []);
                    if (!$actionInstance->handle()->executed()) {
                        throw new TransitionActionException("$actionClass[0] ");
                    }
                }
                $transitionFound = true;
                break;
            }
        }

        if (!$transitionFound) {
            // todo exception
        }

        // history actions
        //throw new WorkflowNotSupportedException();
        //throw new WorkflowNotFoundException();

        $self = self::class;
        $this->{$self::stateAttribute()} = $toState;
        $this->{$self::stateMetadataAttribute()} = [
            'latest_from_state' => $this->currentState(),
            'latest_transition_key' => $transitionKeyItem,
            'latest_actions' => $actions
        ];


        return $this->save();
    }
}
