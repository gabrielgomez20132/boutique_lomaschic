<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('id_encargado')->unsigned();
            $table->integer('id_cliente')->unsigned();
            $table->integer('id_type')->unsigned();
            $table->float('monto', 8, 2)->unsigned();
            $table->float('pago_efec', 8, 2)->unsigned();
            $table->float('pago_tarj', 8, 2)->unsigned();
            $table->float('descuento', 8, 2)->unsigned();
            $table->boolean('completada');
            $table->boolean('deHoy');
            $table->integer('id_deuda')->unsigned()->default(0);
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
        Schema::dropIfExists('orders');
    }
}
