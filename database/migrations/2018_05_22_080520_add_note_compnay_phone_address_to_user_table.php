<?php
/**
 * This file is part of user table modification.
 *
 * 
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNoteCompnayPhoneAddressToUserTable extends Migration
{
    /**
     * Determine the user table name.
     *
     * @return string
     */
    public function getUserTableName()
    {
        $user_model = config('auth.providers.users.model', App\User::class);

        return (new $user_model)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->getUserTableName(), function (Blueprint $table) {
            $table->string('company_phone')->nullable();
			$table->string('company_address')->nullable();
            $table->text('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->getUserTableName(), function (Blueprint $table) {
            $table->dropColumn('company_phone');
			$table->dropColumn('company_address');
            $table->dropColumn('note');
        });
    }
}
