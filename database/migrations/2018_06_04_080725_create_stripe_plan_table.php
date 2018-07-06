<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStripePlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('plan_id')->unique();
            $table->string('nick_name')->unique();
            $table->decimal('price');
            $table->string('currency');
            $table->integer('trial_expiry')->nullable();
            $table->string('product_name');
            $table->boolean('status');
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
        Schema::dropIfExists('stripe_plan');
    }
}
