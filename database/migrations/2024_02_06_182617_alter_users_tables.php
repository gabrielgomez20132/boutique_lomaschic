<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement("ALTER TABLE `users` ADD `role_id` INT NOT NULL DEFAULT '1' AFTER `id_uType`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('ALTER TABLE `users` DROP COLUMN `role_id`');
    }
}
