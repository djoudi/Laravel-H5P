<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateH5pEventLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_event_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('sub_type');
            $table->string('content_id');
            $table->string('content_title');
            $table->string('library_name');
            $table->string('library_version');
            $table->string('user_id');
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
        Schema::drop('h5p_event_logs');
    }
}
