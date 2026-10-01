<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescuentoToAfipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('afip', function (Blueprint $table) {
            $table->decimal('descuento', 7, 2)->after('impIVA');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('afip', function (Blueprint $table) {
            $table->dropColumn('descuento');
        });
    }
}
