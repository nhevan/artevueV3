<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompositeKeyToArtInteractionUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('art_interaction_user', function (Blueprint $table) {
            $table->unique(['user_id', 'art_interaction_id'], 'user_id_art_interaction_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('art_interaction_user', function (Blueprint $table) {
            $table->dropUnique('user_id_art_interaction_id_unique');
        });
    }
}
