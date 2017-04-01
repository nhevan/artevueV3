<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followers', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('follower_id')->unsigned();
            $table->foreign('follower_id')->references('id')->on('users');

            $table->unique(['user_id', 'follower_id'], 'user_id_follower_id_unique');

            $table->integer('like_count')->unsigned()->default(0);
            $table->integer('pin_count')->unsigned()->default(0);
            $table->integer('comment_count')->unsigned()->default(0);
            $table->integer('message_count')->unsigned()->default(0);

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
        Schema::dropIfExists('followers');
    }
}
