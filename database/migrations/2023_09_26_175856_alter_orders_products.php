<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('ALTER TABLE `orders_products` MODIFY `costo_x_uni` DOUBLE(12,3) NOT NULL');
        DB::statement('ALTER TABLE `orders_products` MODIFY `monto` DOUBLE(12,2) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('ALTER TABLE `orders_products` MODIFY `costo_x_uni` DOUBLE(8,3) NOT NULL');
        DB::statement('ALTER TABLE `orders_products` MODIFY `monto` DOUBLE(8,2) NOT NULL');
    }
}
