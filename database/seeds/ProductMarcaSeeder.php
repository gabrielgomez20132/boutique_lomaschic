<?php

use Illuminate\Database\Seeder;
use App\ProductMarca;

class ProductMarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $marcas = [
            'Nike',
            'Adidas',
            'Puma',
            'Reebok',
            'Under Armour',
            'New Balance',
            'Converse',
            'Vans',
            'Fila',
            'Lacoste',
            'Sin Marca',
        ];

        foreach ($marcas as $marca) {
            ProductMarca::create([
                'nombre' => $marca,
                'activa' => true
            ]);
        }
    }
}