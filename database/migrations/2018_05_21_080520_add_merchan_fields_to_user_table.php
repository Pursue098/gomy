<?php
/**
 * This file is part of user table modification.
 *
 * 
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMerchanFieldsToUserTable extends Migration
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
            $table->string('company_info')->nullable();
            $table->string('address')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('sale_points')->nullable();
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
            $table->dropColumn('company_info');
            $table->dropColumn('address');
            $table->dropColumn('vat_number');
            $table->dropColumn('sale_points');
        });
    }
}
