<?php

use App\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
public function run()
    {
        $categorias = [
            'Calzado',
            'Ropa',
            'Accesorios',
            'Equipamiento',
            'Bolsos',
        ];

        foreach ($categorias as $categoria) {
            ProductCategory::create([
                'nombre' => $categoria,
                'unidad' => 'Un',
                'activa' => true
            ]);
        }
    }
}
