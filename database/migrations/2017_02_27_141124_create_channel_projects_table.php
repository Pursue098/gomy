<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_project', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('project_id')->unsigned();
            $table->integer('channel_id')->unsigned();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('channel_id')->references('id')->on('channels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_project');
    }
}
