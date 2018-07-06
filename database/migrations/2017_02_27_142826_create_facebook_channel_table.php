<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacebookChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ch_facebook', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();
            $table->string('name');
            $table->string('status', 20)->default('new');
            $table->string('username')->nullable()->default(null);
            $table->string('link');
            $table->string('picture', 300)->nullable()->default(null);
            $table->string('category')->index();
            $table->date('founded')->nullable()->default(null);
            $table->integer('fan_count');
            $table->boolean('is_webhooks_subscribed');
            $table->boolean('has_added_app');
            $table->string('access_token', 300);
            $table->integer('posts')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('mentions')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('ratings')->default(0);
            $table->string('app_name', 100)->nullable()->default(null);
            $table->string('app_id', 100)->nullable()->default(null);
            $table->string('app_secret', 300)->nullable()->default(null);

            $table->decimal('coefficient_social_login', 5, 2)->default(10);
            $table->decimal('coefficient_post', 5, 2)->default(1);
            $table->decimal('coefficient_comment', 5, 2)->default(1);
            $table->decimal('coefficient_like', 5, 2)->default(1);
            $table->decimal('coefficient_mention', 5, 2)->default(2);
            $table->decimal('coefficient_influence', 5, 2)->default(1);
            $table->decimal('coefficient_like_received', 5, 2)->default(0.5);
            $table->decimal('coefficient_share_received', 5, 2)->default(0.8);
            $table->decimal('coefficient_comment_received', 5, 2)->default(1.2);

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
        Schema::dropIfExists('ch_facebook');
    }
}
