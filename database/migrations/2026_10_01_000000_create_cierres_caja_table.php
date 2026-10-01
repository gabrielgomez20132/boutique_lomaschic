<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCierresCajaTable extends Migration
{
    public function up()
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->dateTime('apertura')->nullable();
            $table->dateTime('cierre');
            $table->string('cerrado_por', 100)->nullable();
            $table->integer('cant_ventas')->default(0);
            $table->decimal('total_efectivo', 12, 2)->default(0);
            $table->decimal('total_turno', 12, 2)->default(0);
            $table->longText('resumen'); // snapshot completo del turno (JSON)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cierres_caja');
    }
}
