<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')->references('id')->on('users');

            $table->integer('artist_id')->nullable()->unsigned();
            $table->foreign('artist_id')->references('id')->on('artists');
            
            $table->string('image');
            $table->text('description')->nullable();
            $table->text('hashtags')->nullable();
            $table->float('aspect_ratio')->default(1);
            $table->float('price')->default(0);
            $table->integer('has_buy_btn')->default(0);
            $table->integer('is_public')->default(1);
            $table->integer('is_gallery_item')->default(0);
            $table->integer('is_locked')->default(0);
            $table->integer('sequence')->default(0);

            $table->string('google_place_id')->nullable();
            $table->string('address_title')->nullable();
            $table->string('address')->nullable();

            $table->integer('pin_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('like_count')->default(0);

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
        Schema::dropIfExists('posts');
    }
}
