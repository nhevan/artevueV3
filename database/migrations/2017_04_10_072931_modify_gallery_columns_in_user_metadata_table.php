<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyGalleryColumnsInUserMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_metadata', function (Blueprint $table) {
            $table->text('gallery_name')->change();
            $table->text('gallery_description')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_metadata', function (Blueprint $table) {
            $table->string('gallery_name', 45)->change();
            $table->string('gallery_description', 45)->change();
        });
    }
}
