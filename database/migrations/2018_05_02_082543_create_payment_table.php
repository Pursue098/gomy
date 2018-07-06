<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('channel_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('gateway');
            $table->string('gateway_mode');
            $table->integer('amount');
            $table->decimal('tax');
            $table->string('type'); // monthly or yearly
            $table->string('description');
            $table->string('transaction_id');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('channel_id')->references('id')->on('channels')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment');
    }
}
