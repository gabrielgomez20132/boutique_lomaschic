<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevolucionProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devolucion_productos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('id_devolucion')->unsigned();
            $table->integer('id_producto')->unsigned();
            $table->float('cantidad_devuelta', 8, 3)->unsigned();
            $table->float('precio_unitario', 8, 2)->unsigned();
            $table->float('subtotal', 8, 2)->unsigned();
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
        Schema::dropIfExists('devolucion_productos');
    }
}
