<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateH5pLibrariesLibrariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_libraries_libraries', function (Blueprint $table) {
            $table->bigInteger('library_id')->unsigned();
            $table->bigInteger('required_library_id')->unsigned();
            $table->string('dependency_type', 31);
            $table->primary(['library_id', 'required_library_id'], 'fk_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('h5p_libraries_libraries');
    }
}
