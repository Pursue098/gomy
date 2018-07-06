<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenidTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('
            CREATE TABLE `openid_clients` (
              `client_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `project_id` int(10) unsigned NOT NULL,
              `channel_id` int(10) unsigned NOT NULL,
              `client_secret` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `redirect_uri` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
              `grant_types` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
              `scope` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `user_id` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`client_id`),
              KEY `openid_clients_project_id_foreign` (`project_id`),
              KEY `openid_clients_channel_id_foreign` (`channel_id`),
              CONSTRAINT `openid_clients_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`),
              CONSTRAINT `openid_clients_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        DB::statement('
            CREATE TABLE `openid_access_tokens` (
              `access_token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
              `client_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
              `user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`access_token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        DB::statement('
            CREATE TABLE `openid_authorization_codes` (
              `authorization_code` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
              `client_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
              `user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `redirect_uri` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
              `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
              `id_token` text COLLATE utf8_unicode_ci,
              PRIMARY KEY (`authorization_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        \DB::statement('
            CREATE TABLE `openid_scopes` (
              `scope` text COLLATE utf8_unicode_ci,
              `is_default` tinyint(1) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        \DB::table('openid_scopes')->insert([
            ['scope' => 'email', 'is_default' => 1],
            ['scope' => 'profile', 'is_default' => 1],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openid_clients');
        Schema::dropIfExists('openid_access_tokens');
        Schema::dropIfExists('openid_authorization_codes');
        Schema::dropIfExists('openid_scopes');
    }
}
