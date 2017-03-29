<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('sex')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('profile_picture', 255)->nullable();
            $table->string('website', 100)->nullable();
            $table->text('biography')->nullable();
            $table->text('device_token')->nullable();
            $table->text('gcm_registration_key')->nullable();
            $table->text('social_media')->nullable();
            $table->text('social_media_uid')->nullable();
            $table->text('social_media_access_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sex', 'dob', 'phone', 'profile_picture', 'website', 'biography', 'device_token', 'gcm_registration_key', 'social_media', 'social_media_uid', 'social_media_access_token']);
        });
    }
}
