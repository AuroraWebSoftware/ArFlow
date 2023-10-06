<?php

namespace AuroraWebSoftware\ArFlow\Traits;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Exceptions\InitialStateNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\TransitionNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotFoundException;
use AuroraWebSoftware\ArFlow\Exceptions\WorkflowNotSupportedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Throwable;

trait HasState
{

    /**
     * returns all workflows
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
     * @param string $workflow
     * @return bool
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

    public function appliedWorkflow(): string
    {

        $self = self::class;

        /**
         * @var Model&StateableModelContract $self
         */
        return $this->getAttribute($self::workflowAttribute());
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
     * @throws WorkflowNotFoundException|Throwable
     */
    public function canTransitionTo(string $state, array $withoutGuards = null): bool
    {

        $appliedWorkflowValue = Config::get('arflow.workflows')[$this->appliedWorkflow()];
        throw_unless($appliedWorkflowValue, WorkflowNotFoundException::class, $this->appliedWorkflow() . ' Not Found');

        $transitions = $appliedWorkflowValue['transitions'] ?? null;
        throw_unless($transitions, TransitionNotFoundException::class);

        foreach ($transitions as $transition) {
            $from = $transition['from'];

            if ($this->currentState() == $from || in_array($this->currentState(), $from)) {

            }

        }

        $guard1 = App::make(TestAllowedTransitionGuard::class);

        dd($appliedWorkflow);


        // dd($workflows);

    }

    public function possibleTransitions(array $withoutGuards = null): ?array
    {
        return null;
    }

    public function transitionTo(
        string $state, string $comment = null,
        string $byModelType = null, int $byModelId = null,
        array  $metadata = null,
        array  $withoutGuards = null,
        bool   $transitionHistoryAction = true
    ): bool
    {

        //throw new WorkflowNotSupportedException();
        //throw new WorkflowNotFoundException();

        return false;
    }
}
