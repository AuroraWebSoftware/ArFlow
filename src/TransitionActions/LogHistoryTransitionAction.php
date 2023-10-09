<?php

namespace AuroraWebSoftware\ArFlow\TransitionActions;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Contacts\TransitionActionContract;
use AuroraWebSoftware\ArFlow\StateTransition;
use Illuminate\Database\Eloquent\Model;

class LogHistoryTransitionAction implements TransitionActionContract
{
    private array $parameters;
    private StateableModelContract&Model $model;
    private string $from;
    private string $to;

    public function boot(StateableModelContract&Model $model, string $from, string $to, array $parameters = []): void
    {
        $this->model = $model;
        $this->from = $from;
        $this->to = $to;
        $this->parameters = $parameters;
    }

    public function handle(): void
    {
        StateTransition::create([
            'workflow' => $this->model->currentWorkflow(),
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id,
            'from' => $this->from,
            'to' => $this->to,
            'actor_model_type' => $this->parameters['actor_model_type'],
            'actor_model_id' => $this->parameters['actor_model_id'],
            'comment' => $this->parameters['comment'] ?? null,
            'metadata' => $this->parameters['metadata'] ?? null,
        ]);
    }

    public function failed(): void
    {
        // TODO: Implement failed() method.
    }
}
