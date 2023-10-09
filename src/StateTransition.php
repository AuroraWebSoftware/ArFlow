<?php

namespace AuroraWebSoftware\ArFlow;

use Illuminate\Database\Eloquent\Model;

class StateTransition extends Model
{
    protected $table = 'arflow_state_transitions';
    protected $guarded = [];
}