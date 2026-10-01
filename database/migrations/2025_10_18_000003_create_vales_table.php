<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vales', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('codigo_vale', 50)->unique();
            $table->integer('id_cliente')->unsigned();
            $table->float('monto_original', 8, 2)->unsigned();
            $table->float('monto_usado', 8, 2)->unsigned()->default(0);
            $table->float('monto_disponible', 8, 2)->unsigned();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->integer('id_devolucion')->unsigned()->nullable();
            $table->boolean('activo')->default(true);
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
        Schema::dropIfExists('vales');
    }
}
