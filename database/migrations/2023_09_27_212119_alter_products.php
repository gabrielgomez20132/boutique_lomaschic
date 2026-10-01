<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('ALTER TABLE `products` MODIFY `costo` DOUBLE(12,3) NOT NULL');
        DB::statement('ALTER TABLE `products` MODIFY `monto` DOUBLE(12,2) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('ALTER TABLE `products` MODIFY `costo` DOUBLE(12,3) NOT NULL');
        DB::statement('ALTER TABLE `products` MODIFY `monto` DOUBLE(12,2) NOT NULL');
    }
}
