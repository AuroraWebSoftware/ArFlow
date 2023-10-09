<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class create_arflow_history_table extends Migration
{
    public function up() : void
    {
        Schema::create('arflow_state_transitions', function (Blueprint $table) {
            $table->id();
            $table->string('workflow');
            $table->string('model_type')->index();
            $table->integer('model_id')->index();
            $table->string('from')->nullable();
            $table->string('to');
            $table->string('actor_model_type')->nullable();
            $table->integer('actor_model_id')->nullable();
            $table->string('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::drop('arflow_state_transitions');
    }
}
