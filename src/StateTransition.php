<?php

namespace AuroraWebSoftware\ArFlow;

use Illuminate\Database\Eloquent\Model;

class StateTransition extends Model
{
    protected $table = 'arflow_state_transitions';

    protected $guarded = [];

    public int $actor_model_id;
    public string $actor_model_type;
    public string $workflow;
    public string $from;
    public string $to;
    public string $comment;
    public array $metadata;
    public int $model_id;
    public string $model_type;

}
