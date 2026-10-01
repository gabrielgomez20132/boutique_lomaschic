<?php

use Illuminate\Database\Seeder;
use App\OrderProduct;
use App\Product;

class GenerateIngresoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productos = Product::all();
        foreach ($productos as $producto) {
            $cantidad = OrderProduct::with('order')
                ->where('id_producto', $producto->id)
                ->whereHas('order', function ($query) {
                    $query->where('completada', '!=', 0);
                })
                ->sum('cantidad');

            DB::table('ingreso_producto')->insert([
                'cantidad' => $cantidad + $producto->quedan,
                'costo' => $producto->costo,
              	'monto' => $producto->monto,
                'id_producto' => $producto->id,
                'id_user' => 1, // Assuming user ID 1 exists
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
