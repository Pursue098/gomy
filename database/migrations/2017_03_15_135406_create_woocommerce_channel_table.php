<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWoocommerceChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ch_woocommerce', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url')->unique();
            $table->string('name');
            $table->string('status', 20)->default('new');
            $table->string('consumer_key')->nullable();
            $table->string('consumer_secret')->nullable();
            $table->string('description');
            $table->string('version', 10);
            $table->string('timezone', 20);
            $table->string('currency', 5);
            $table->text('picture')->nullable();

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
        Schema::dropIfExists('ch_woocommerce');
    }
}
