<?php

use Illuminate\Database\Seeder;
use App\ProductTalle;

class ProductTalleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $talles = [
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL',
            'XXXL',
            '32',
            '33',
            '34',
            '35',
            '36',
            '37',
            '38',
            '39',
            '40',
            '41',
            '42',
            '43',
            '44',
            '45',
            '46',
            '48',
            '50',
            '52',
            'Único',
            'Talle Libre',
            'One Size'
        ];

        foreach ($talles as $talle) {
            ProductTalle::create([
                'nombre' => $talle,
                'activa' => true
            ]);
        }
    }
}