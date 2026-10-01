<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFormasPagoNombres extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
/*        Schema::table('formas_pago', function (Blueprint $table) {
            $table->string('nombre', 200)->change();
        });*/
        DB::statement('ALTER TABLE `formas_pago` MODIFY `nombre` VARCHAR(200) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
     /* Schema::table('formas_pago', function (Blueprint $table) {
            $table->string('nombre', 15)->change();
        });*/
        DB::statement('ALTER TABLE `formas_pago` MODIFY `nombre` VARCHAR(200) NOT NULL');
    }
}
