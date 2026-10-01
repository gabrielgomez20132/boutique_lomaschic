<?php

namespace App\Http\Controllers;

use App\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        $orders = Order::with(['productos.producto.category'])
        ->where('created_at', '>=', Carbon::now()->subWeek())
        ->get();

        $type = "Reporte";

        return view('reporte.index', compact('type'));
    }

    public function mes() {
        $orders = Order::with(['productos.producto.category'])
        ->where('created_at', '>=', Carbon::now()->subMonth())
        ->get();

        return response()->json($orders);
    }

    public function semana() {
        $orders = Order::with(['productos.producto.category'])
        ->where('created_at', '>=', Carbon::now()->subWeek())
        ->get();

        return response()->json($orders);
    }

    public function personalizado(Request $request)
    {
        $inicio = $request->query('inicio');
        $fin = $request->query('fin');

        // Validaciones básicas
        if (!$inicio || !$fin) {
            return response()->json(['error' => 'Fechas inválidas'], 400);
        }

        try {
            $inicioParsed = Carbon::parse($inicio)->startOfDay();
            $finParsed = Carbon::parse($fin)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de fecha inválido'], 400);
        }

        $orders = Order::with(['productos.producto.category'])
            ->whereBetween('created_at', [$inicioParsed, $finParsed])
            ->get();

        return response()->json($orders);
    }
}
