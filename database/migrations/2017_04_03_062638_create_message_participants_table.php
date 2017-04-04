<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_participants', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('participant_one')->unsigned();
            $table->foreign('participant_one')->references('id')->on('users');

            $table->integer('participant_two')->unsigned();
            $table->foreign('participant_two')->references('id')->on('users');

            $table->unique(['participant_one', 'participant_two'], 'participant_one_participant_two_unique');

            $table->integer('last_message_id')->unsigned();
            $table->foreign('last_message_id')->references('id')->on('messages');

            $table->integer('total_messages')->default(1);

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
        Schema::dropIfExists('message_participants');
    }
}
