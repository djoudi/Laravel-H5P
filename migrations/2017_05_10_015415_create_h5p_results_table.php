<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateH5pResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5p_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('content_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('score')->unsigned();
            $table->bigInteger('max_score')->unsigned();
            $table->bigInteger('opened')->unsigned();
            $table->bigInteger('finished')->unsigned();
            $table->bigInteger('time')->unsigned();
            $table->index(['content_id', 'user_id'], 'content_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('h5p_results');
    }
}
