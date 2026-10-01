<?php

namespace App\Http\Controllers;

use App\Devolucion;
use App\DevolucionProducto;
use App\Order;
use App\OrderProduct;
use App\Product;
use App\Vale;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $devoluciones = Devolucion::with(['orderOriginal', 'cliente', 'encargado', 'vale'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('devoluciones.index', compact('devoluciones'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtener órdenes completadas para seleccionar
        $orders = Order::where('completada', 1)
            ->with(['cliente', 'productos.producto'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Preparar datos para el buscador Vue
        $ordersData = $orders->map(function($order) {
            return [
                'id' => $order->id,
                'cliente_nombre' => $order->cliente->nombre ?? 'Cliente General',
                'monto' => $order->monto,
                'monto_formateado' => number_format($order->monto, 2),
                'fecha' => $order->created_at->format('d/m/Y'),
                'texto_busqueda' => $order->id . ' ' . ($order->cliente->nombre ?? 'Cliente General') . ' ' . $order->created_at->format('d/m/Y')
            ];
        });

        return view('devoluciones.create', compact('orders', 'ordersData'));
    }

    /**
     * Obtener detalles de una orden por AJAX
     *
     * @param  int  $id_order
     * @return \Illuminate\Http\Response
     */
    public function getOrderDetails($id_order)
    {
        $order = Order::with(['productos.producto', 'cliente'])->find($id_order);

        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        return response()->json($order);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_order_original' => 'required|exists:orders,id',
            'productos' => 'required|string',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Decodificar JSON de productos
        $productos = json_decode($request->productos, true);
        if (!$productos || count($productos) === 0) {
            return back()->withErrors(['error' => 'Debe seleccionar al menos un producto para devolver.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $order = Order::find($request->id_order_original);

            // Verificar que la orden esté completada
            if ($order->completada != 1) {
                return back()->withErrors(['error' => 'Solo se pueden procesar devoluciones de órdenes completadas.'])->withInput();
            }

            $id_encargado = Auth::id();
            $monto_total = 0;

            // Validar y calcular monto total de la devolución
            foreach ($productos as $prod) {
                // Verificar que la cantidad devuelta no exceda la cantidad original
                $order_product = OrderProduct::where('id_order', $order->id)
                    ->where('id_producto', $prod['id_producto'])
                    ->first();

                if (!$order_product) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Producto no encontrado en la orden original.'])->withInput();
                }

                if ($prod['cantidad_devuelta'] > $order_product->cantidad) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'La cantidad a devolver no puede ser mayor a la cantidad original.'])->withInput();
                }

                $subtotal = $prod['cantidad_devuelta'] * $prod['precio_unitario'];
                $monto_total += $subtotal;
            }

            // Crear la devolución
            $devolucion = Devolucion::create([
                'id_order_original' => $request->id_order_original,
                'id_cliente' => $order->id_cliente,
                'id_encargado' => $id_encargado,
                'monto_total' => $monto_total,
                'observaciones' => $request->observaciones,
            ]);

            // Procesar cada producto devuelto
            foreach ($productos as $prod) {
                $cantidad_devuelta = $prod['cantidad_devuelta'];
                $precio_unitario = $prod['precio_unitario'];
                $subtotal = $cantidad_devuelta * $precio_unitario;

                // Crear detalle de devolución
                DevolucionProducto::create([
                    'id_devolucion' => $devolucion->id,
                    'id_producto' => $prod['id_producto'],
                    'cantidad_devuelta' => $cantidad_devuelta,
                    'precio_unitario' => $precio_unitario,
                    'subtotal' => $subtotal,
                ]);

                // Devolver productos al inventario
                $product = Product::find($prod['id_producto']);
                if ($product) {
                    $product->increment('quedan', $cantidad_devuelta);
                }
            }

            // Generar vale por el monto de la devolución
            $vale = Vale::create([
                'codigo_vale' => Vale::generarCodigo(),
                'id_cliente' => $order->id_cliente,
                'monto_original' => $monto_total,
                'monto_usado' => 0,
                'monto_disponible' => $monto_total,
                'fecha_emision' => now(),
                'fecha_vencimiento' => now()->addDays(15),
                'id_devolucion' => $devolucion->id,
                'activo' => true,
            ]);

            DB::commit();

            // Redirigir al ticket para imprimir automáticamente
            return redirect()->route('devoluciones.ticket', $devolucion->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar la devolución: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Devolucion  $devolucion
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $devolucion = Devolucion::with([
            'orderOriginal.productos.producto',
            'productos.producto',
            'cliente',
            'encargado',
            'vale'
        ])->findOrFail($id);

        return view('devoluciones.show', compact('devolucion'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Devolucion  $devolucion
     * @return \Illuminate\Http\Response
     */
    public function edit(Devolucion $devolucion)
    {
        // No se permite editar devoluciones por ahora
        return redirect()->route('devoluciones.index')
            ->with('warning', 'No se permite editar devoluciones.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Devolucion  $devolucion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Devolucion $devolucion)
    {
        // No se permite actualizar devoluciones por ahora
        return redirect()->route('devoluciones.index')
            ->with('warning', 'No se permite modificar devoluciones.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Devolucion  $devolucion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Devolucion $devolucion)
    {
        // No se permite eliminar devoluciones por ahora
        return redirect()->route('devoluciones.index')
            ->with('warning', 'No se permite eliminar devoluciones.');
    }

    /**
     * Imprimir ticket del vale generado
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function imprimirTicket($id)
    {
        $devolucion = Devolucion::with([
            'orderOriginal',
            'productos.producto',
            'cliente',
            'encargado',
            'vale'
        ])->findOrFail($id);

        $vale = $devolucion->vale;

        if (!$vale) {
            return redirect()->route('devoluciones.show', $id)
                ->with('error', 'No se encontró el vale asociado a esta devolución.');
        }

        return view('devoluciones.ticket-vale', compact('devolucion', 'vale'));
    }
}
