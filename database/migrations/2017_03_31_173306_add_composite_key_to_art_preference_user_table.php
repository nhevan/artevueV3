<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompositeKeyToArtPreferenceUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('art_preference_user', function (Blueprint $table) {
            $table->unique(['user_id', 'art_preference_id'], 'user_id_art_preference_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('art_preference_user', function (Blueprint $table) {
            $table->dropUnique('user_id_art_preference_id_unique');
        });
    }
}
