<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAfip extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement('ALTER TABLE `afip` MODIFY `impNeto` DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `impIVA` DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `descuento` DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `impTotal` DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `montoOpcional` DECIMAL(12,2) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('ALTER TABLE `afip` MODIFY `impNeto` DECIMAL(7,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `impIVA` DECIMAL(7,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `descuento` DECIMAL(7,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `impTotal` DECIMAL(7,2) NOT NULL');
        DB::statement('ALTER TABLE `afip` MODIFY `montoOpcional` DECIMAL(7,2) NOT NULL');
    }
}
