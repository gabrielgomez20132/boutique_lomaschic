<?php

use Illuminate\Database\Seeder;

class FormaPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('formas_pago')->truncate();

        DB::table('formas_pago')->insert(['id' => 1, 'nombre' => 'Efectivo']);
        DB::table('formas_pago')->insert(['id' => 2, 'nombre' => 'Tarjeta']);
        DB::table('formas_pago')->insert(['id' => 3, 'nombre' => 'Efectivo/Tarjeta']);
        DB::table('formas_pago')->insert(['id' => 4, 'nombre' => 'FIADO']);
        DB::table('formas_pago')->insert(['id' => 5, 'nombre' => 'Transferencia']);
        DB::table('formas_pago')->insert(['id' => 6, 'nombre' => 'Banco Nacion Marcaton']);
        DB::table('formas_pago')->insert(['id' => 7, 'nombre' => 'Cheque/Dolares']);
        DB::table('formas_pago')->insert(['id' => 8, 'nombre' => 'Efectivo/Transferencia']);
        DB::table('formas_pago')->insert(['id' => 9, 'nombre' => 'Cheque']);
        DB::table('formas_pago')->insert(['id' => 10, 'nombre' => 'Efectivo/Dolares']);
        DB::table('formas_pago')->insert(['id' => 11, 'nombre' => 'Transferencia/Tarjeta']);
        DB::table('formas_pago')->insert(['id' => 12, 'nombre' => 'Transferencia/Cheque']);
        DB::table('formas_pago')->insert(['id' => 13, 'nombre' => 'Transferencia/Dolares']);
        DB::table('formas_pago')->insert(['id' => 14, 'nombre' => 'Tarjeta/Cheque']);
        DB::table('formas_pago')->insert(['id' => 15, 'nombre' => 'Tarjeta/Dolares']);
        DB::table('formas_pago')->insert(['id' => 16, 'nombre' => 'Cheque/Dolares']);
        DB::table('formas_pago')->insert(['id' => 17, 'nombre' => 'Credito/McCred']);
        DB::table('formas_pago')->insert(['id' => 18, 'nombre' => 'Credito/Quilmes']);
        DB::table('formas_pago')->insert(['id' => 19, 'nombre' => 'Vale']);
        DB::table('formas_pago')->insert(['id' => 20, 'nombre' => 'Efectivo/Vale']);
        DB::table('formas_pago')->insert(['id' => 21, 'nombre' => 'Tarjeta/Vale']);
        DB::table('formas_pago')->insert(['id' => 22, 'nombre' => 'Transferencia/Vale']);
        DB::table('formas_pago')->insert(['id' => 23, 'nombre' => 'Cheque/Vale']);
        DB::table('formas_pago')->insert(['id' => 24, 'nombre' => 'Mercado Pago']);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
