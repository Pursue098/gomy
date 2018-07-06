<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('channel_type');
            $table->string('name');
            $table->integer('comp_start');
            $table->integer('comp_end');
            $table->integer('price');
            $table->string('plan_id');
            $table->string('plan_name');
            $table->integer('trial_expiry')->nullable();
            $table->integer('status');
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
        Schema::dropIfExists('tiers');
    }
}
