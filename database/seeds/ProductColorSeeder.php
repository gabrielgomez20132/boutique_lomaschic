<?php

use Illuminate\Database\Seeder;
use App\ProductColor;

class ProductColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $colores = [
            'Amarillo',
            'Azul',
            'Beige',
            'Blanco',
            'Celeste',
            'Dorado',
            'Gris',
            'Marrón',
            'Naranja',
            'Negro',
            'Plateado',
            'Rojo',
            'Bordo',
            'Rosa',
            'Verde',
            'Violeta',
        ];

        foreach ($colores as $color) {
            ProductColor::create([
                'nombre' => $color,
                'activa' => true
            ]);
        }
    }
}