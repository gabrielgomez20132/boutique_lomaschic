<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngresoProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingreso_producto', function (Blueprint $table) {
            $table->id();
            $table->float('cantidad', 8, 3)->unsigned();
            $table->float('costo', 10, 2);
            $table->float('monto', 10, 2);
            $table->integer('id_producto')->references('id')->on('products')->unsigned();
            $table->integer('id_user')->references('id')->on('users')->unsigned();
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
        Schema::dropIfExists('ingreso_producto');
    }
}
