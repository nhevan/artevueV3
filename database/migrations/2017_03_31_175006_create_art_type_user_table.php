<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArtTypeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('art_type_user', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('art_type_id')->unsigned();
            $table->foreign('art_type_id')->references('id')->on('art_types');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->unique(['user_id', 'art_type_id'], 'user_id_art_type_id_unique');

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
        Schema::dropIfExists('art_type_user');
    }
}
