<?php
/**
 * This file is part of Poynt call back response and poynt jwt token.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPoyntosFieldsToUserTable extends Migration
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
            $table->text('poynt_response')->nullable();
			$table->text('self_signed_token')->nullable();
			$table->text('poynt_response_token')->nullable();
			$table->text('device_id')->nullable();
			$table->text('business_id')->nullable();
			
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
            $table->dropColumn('poynt_response');
			$table->dropColumn('self_signed_token');
			$table->dropColumn('poynt_response_token');
			$table->dropColumn('device_id');
			$table->dropColumn('business_id');
        });
    }
}
