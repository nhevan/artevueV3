<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueKeyConstraintFromPinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pins', function (Blueprint $table) {

            $table->dropForeign(['user_id']);
            $table->dropUnique('user_id_post_id_unique');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->unique(['user_id', 'post_id'], 'user_id_post_id_unique');
        });
    }
}
