<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('project_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('email');
            $table->enum('role', ['user', 'admin']);
            $table->enum('status', ['pending', 'successful','canceled','expired']);
            $table->datetime('valid_till');
            $table->string('code', 32)->index();
            $table->timestamps();

            $table->unique(['project_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invites');
    }
}
