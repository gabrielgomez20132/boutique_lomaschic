<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('id_categoria')->unsigned();
            $table->integer('id_talle')->unsigned();
            $table->integer('id_color')->unsigned();
            $table->string('codigo')->unique();
            $table->string('nombre', 55);
            $table->float('pedido', 8, 3)->unsigned();
            $table->float('quedan', 8, 3)->unsigned();
            $table->float('aviso', 8, 3)->unsigned();
            $table->float('costo', 8, 2);
            $table->float('monto', 8, 2);
            $table->string('archivo', 25);
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
        Schema::dropIfExists('products');
    }
}
