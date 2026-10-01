<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('afip', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->dateTime('cbteFch');
            $table->boolean('tipoCbteNum');
            $table->string('nroCbte', 17);
            $table->string('caeNum', 17);
            $table->date('caeFvt');
            $table->tinyInteger('docTipo');
            $table->string('docNro', 15);
            $table->string('nombreRS', 35)->nullable();
            $table->char('tipoPago', 4);
            $table->decimal('impNeto', 7, 2);
            $table->decimal('impIVA', 7, 2);
            $table->decimal('impTotal', 7, 2);
            $table->boolean('pagada');
            $table->string('nroCbteAsoc', 17)->nullable();
            $table->string('codigoBarra', 45)->nullable();
            $table->string('detalleOpcional', 100)->nullable();
            $table->decimal('montoOpcional', 7, 2)->nullable();
            $table->string('concepto', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('afip');
    }
}
