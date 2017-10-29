<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostArtTypeFieldInPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('post_art_type_id')->unsigned()->default(10); //defauls to other post art types
            $table->foreign('post_art_type_id')->references('id')->on('post_art_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign('posts_post_art_type_id_foreign');
            $table->dropColumn('post_art_type_id');
        });
    }
}
