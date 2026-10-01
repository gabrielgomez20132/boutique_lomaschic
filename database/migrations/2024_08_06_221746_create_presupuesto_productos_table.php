<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePresupuestoProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presupuesto_productos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_presupuesto');
            $table->integer('id_producto');
            $table->double('cantidad', 14, 2);
            $table->double('costo_x_uni', 14, 2);
            $table->double('monto', 14, 2);
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
        Schema::dropIfExists('presupuesto_productos');
    }
}
