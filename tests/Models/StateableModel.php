<?php

namespace AuroraWebSoftware\ArFlow\Tests\Models;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Traits\HasState;
use Illuminate\Database\Eloquent\Model;

class StateableModel extends Model implements StateableModelContract
{
    use HasState;

    public static function supportedWorkflows(): array
    {
        return ['a', 'b'];
    }
}