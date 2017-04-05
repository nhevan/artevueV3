<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHashtagPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hashtag_post', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('post_id')->unsigned();
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');

            $table->integer('hashtag_id')->unsigned();
            $table->foreign('hashtag_id')->references('id')->on('hashtags')->onDelete('cascade');

            $table->unique(['post_id', 'hashtag_id'], 'post_id_hashtag_id_unique');

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
        Schema::dropIfExists('hashtag_post');
    }
}
