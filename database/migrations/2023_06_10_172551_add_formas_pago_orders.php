<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormasPagoOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('pago_transf', 8, 2)->unsigned()->after('pago_tarj');
            $table->float('pago_cheque', 8, 2)->unsigned()->after('pago_transf');
            $table->float('pago_dolares', 8, 2)->unsigned()->after('pago_cheque');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pago_transf');
            $table->dropColumn('pago_cheque');
            $table->dropColumn('pago_dolares');
        });
    }
}
