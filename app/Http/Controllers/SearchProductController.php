<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class SearchProductController extends Controller
{
    public function search($keywords)
    {
        $productos = Product::where(function($query) use ($keywords) {
            $query->where('nombre', 'LIKE', "%$keywords%")
                  ->orWhere('codigo', 'LIKE', "%$keywords%")
                  ->orWhereHas('category', function($q) use ($keywords) {
                      $q->where('nombre', 'LIKE', "%$keywords%");
                  })
                  ->orWhereHas('marca', function($q) use ($keywords) {
                      $q->where('nombre', 'LIKE', "%$keywords%");
                  })
                  ->orWhereHas('talle', function($q) use ($keywords) {
                      $q->where('nombre', 'LIKE', "%$keywords%");
                  })
                  ->orWhereHas('color', function($q) use ($keywords) {
                      $q->where('nombre', 'LIKE', "%$keywords%");
                  });
        })
        ->orderBy('nombre', 'asc')
        ->with(['category', 'marca', 'talle', 'color'])
        ->get();

        // Agregamos el atributo calculado manualmente a cada producto
        foreach ($productos as $producto) {
            $producto->cantidad_total_ingresada = $producto->getTotalCantidadIngresoProducto();
        }

        return response()->json($productos);
    }
}
