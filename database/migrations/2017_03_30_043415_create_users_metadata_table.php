<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_metadata', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('gallery_name', 45)->nullable();
            $table->string('gallery_description', 45)->nullable();
            $table->string('museum_name', 120)->nullable();
            $table->string('foundation_name', 45)->nullable();

            $table->boolean('is_notification_enabled')->default(true);
            $table->boolean('is_account_private')->default(false);
            $table->boolean('is_save_to_phone')->default(false);
            $table->boolean('is_blocked')->default(false);

            $table->integer('post_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('pin_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->integer('message_count')->default(0);
            $table->integer('follower_count')->default(0);
            $table->integer('following_count')->default(0);
            $table->integer('tagged_count')->default(0);

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
        Schema::dropIfExists('users_metadata');
    }
}
