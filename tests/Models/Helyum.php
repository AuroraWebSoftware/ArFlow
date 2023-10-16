<?php

namespace AuroraWebSoftware\ArFlow\Tests\Models;

use AuroraWebSoftware\ArFlow\Contacts\StateableModelContract;
use AuroraWebSoftware\ArFlow\Traits\HasState;
use Illuminate\Database\Eloquent\Model;

class Helyum extends Model
{
    use HasState;

    public static function supportedWorkflows(): array
    {
        return ['workflow3'];
    }
}
