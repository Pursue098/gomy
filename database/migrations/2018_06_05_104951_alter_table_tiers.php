<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tiers', function (Blueprint $table) {
            
            $table->integer('prod_plan_id')->unsigned()->nullable();
            $table->foreign('prod_plan_id')->references('id')->on('stripe_plan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		 Schema::table('tiers', function (Blueprint $table) {
            $table->dropColumn('prod_plan_id');
        }); 
    }
}
