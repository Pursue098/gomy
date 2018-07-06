<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoyatliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id');
            $table->string('name', 100)->index();
            $table->string('status', 10)->default('active');
            $table->dateTime('start_at')->index();
            $table->dateTime('stop_at')->index()->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('channel_loyalty', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('channel_id')->references('id')->on('channels');
            $table->integer('loyalty_id')->references('id')->on('loyalties');
            $table->json('settings');
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
        Schema::dropIfExists('channel_loyalty');
        Schema::dropIfExists('loyalties');
    }
}