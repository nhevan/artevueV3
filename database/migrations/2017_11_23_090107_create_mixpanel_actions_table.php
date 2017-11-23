<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMixpanelActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mixpanel_actions', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id');
            $table->string('action');
            $table->string('parameters')->nullable();
            $table->string('ip')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mixpanel_actions');
    }
}
