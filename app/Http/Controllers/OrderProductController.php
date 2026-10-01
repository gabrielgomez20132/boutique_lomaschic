<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\OrderProduct;
use App\OrdersServices;
use App\Product;
use App\Order;
use App\ProductCategory;
use Illuminate\Http\Request;

class OrderProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getSubordenes($id_order)
    {
        $orders_svc = \DB::table('orders_services')->select('orders_services.id', DB::raw('1'), 'orders_services.description', 
                                                DB::raw("''"), 'orders_services.monto', 'orders_services.created_at', DB::raw("'sinImagen.png'"), DB::raw("'svc' as tipo"), DB::raw("'' as talle"), DB::raw("'' as color"))
                        ->where('orders_services.id_order', $id_order);

    $orders_indiv = \DB::table('orders_products')
        ->select(
            'orders_products.id',
            'orders_products.cantidad',
            'products.nombre',
            'product_categories.unidad',
            'orders_products.monto',
            'orders_products.created_at',
            'products.archivo',
            \DB::raw("'prod' as tipo"),
            'product_talles.nombre as talle',   // <-- agregado
            'product_colors.nombre as color'    // <-- agregado
        )
        ->join('products', 'products.id', '=', 'orders_products.id_producto')
        ->join('product_categories', 'product_categories.id', '=', 'products.id_categoria')
        ->join('product_talles', 'product_talles.id', '=', 'products.id_talle')   // inner join (es obligatorio)
        ->join('product_colors', 'product_colors.id', '=', 'products.id_color')   // inner join (es obligatorio)
        ->where('orders_products.id_order', $id_order)
        ->unionAll($orders_svc)
        ->get();
        
        return  $orders_indiv;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function store_service(Request $request, $id_order)
    {
        $description = $request->descripcion;
        $monto = $request->monto;
        
        OrdersServices::create([
                'id_order' => $id_order,
                'description' => $description,
                'monto' => $monto
        ]);
            
        Order::where('id', $id_order)->increment('monto', ceil($monto));

        return response()->json(["message" => "+ sericio de " . $description ],200);
    }

    public function store_prod(Request $request, $id_order)
    {
        $product_id = $request->product_id;
        $cant = $request->cantidad;
        
        $product = Product::find($product_id);
        $category = ProductCategory::where('id', $product->id_categoria)->first();
        $unidad = $category->unidad;

        if ($cant == 1 && substr($unidad, -1) == "s") //le quito la S a la unidad (Uds => Ud)
        {
            $unidad = substr($unidad, 0, -1);
        }

        if ($product->quedan >= $cant)
        {
             $monto = $product->monto;

             OrderProduct::create([
                 'id_order' => $id_order,
                 'id_producto' => $product->id,
                 'cantidad' => $cant,
                 'costo_x_uni' => $product->costo,
                 'monto' => $monto
             ]);

             Order::where('id', $id_order)->increment('monto', ceil($monto * $cant));
             $product->decrement('quedan', $cant);

             return response()->json(["message" => "+ " . $cant . " " . $unidad . " de " . $product->nombre, "status" => "OK" ],200);
         }
         else
         {
             return response()->json([
                                        "titulo" => "Imposible vender " . $cant . " " . $unidad,
                                        "message" => "Solo quedan " . $product->quedan . " " . $unidad . " en STOCK"
                                        ],403);
         }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id_order)
    {
        $codigo = $request->codigo;
        $cant = $request->cantidad;
        
        $products = Product::with([
            'talle:id,nombre',
            'color:id,nombre',
        ])
        ->where('codigo', $codigo)
        ->orWhere('nombre', 'like', '%'.$codigo.'%')
        ->get();

        //$product = Product::where('codigo', $codigo)->first();
        switch( count($products) ) {
            case 0:
                return response()->json(["titulo" => "Error",
                                    "message" => "El producto con el código " . $codigo  . " todavía no ha sido cargado!"],403);
                break;
            case 1:
                $product = $products[0];
                $category = ProductCategory::where('id', $product->id_categoria)->first();
                $unidad = $category->unidad;
        
                if ($cant == 1 && substr($unidad, -1) == "s") //le quito la S a la unidad (Uds => Ud)
                {
                    $unidad = substr($unidad, 0, -1);
                }
        
                if ($product->quedan >= $cant) 
                {
                    $monto = $product->monto;
        
                    OrderProduct::create([
                        'id_order' => $id_order,
                        'id_producto' => $product->id,
                        'cantidad' => $cant,
                        'costo_x_uni' => $product->costo,
                        'monto' => $monto
                    ]);
            
                    Order::where('id', $id_order)->increment('monto', ceil($monto * $cant));
                    $product->decrement('quedan', $cant);

                    return response()->json(["message" => "+ " . $cant . " " . $unidad . " de " . $product->nombre, "status" => "OK" ],200);
                } 
                else 
                {
                    return response()->json([
                                            "titulo" => "Imposible vender " . $cant . " " . $unidad,
                                            "message" => "Solo quedan " . $product->quedan . " " . $unidad . " en STOCK"
                                            ],403);
                }
                break;
            default:
                return response()->json(["status" => "QUERY", "products" => $products ],200);
                break;
        }
/*
        if ($product == null) 
        {
            return response()->json(["titulo" => "Error",
                                    "message" => "El producto con el código " . $codigo  . " todavía no ha sido cargado!"],403);
        }
        
        $category = ProductCategory::where('id', $product->id_categoria)->first();
        $unidad = $category->unidad;
        
        if ($cant == 1 && substr($unidad, -1) == "s") //le quito la S a la unidad (Uds => Ud)
        {
            $unidad = substr($unidad, 0, -1);
        }
        
        if ($product->quedan >= $cant) 
        {
            $monto = $product->monto;
        
            OrderProduct::create([
                'id_order' => $id_order,
                'id_producto' => $product->id,
                'cantidad' => $cant,
                'monto' => $monto
            ]);
            
            Order::where('id', $id_order)->increment('monto', ceil($monto * $cant));
            $product->decrement('quedan', $cant);

            return response()->json(["message" => "+ " . $cant . " " . $unidad . " de " . $product->nombre ],200);
        } 
        else 
        {
            return response()->json([
                                    "titulo" => "Imposible vender " . $cant . " " . $unidad,
                                    "message" => "Solo quedan " . $product->quedan . " " . $unidad . " en STOCK"
                                    ],403);
        }
*/
        return;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OrderProduct  $orderProduct
     * @return \Illuminate\Http\Response
     */
    public function show(OrderProduct $orderProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OrderProduct  $orderProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderProduct $orderProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OrderProduct  $orderProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderProduct $orderProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OrderProduct  $orderProduct
     * @return \Illuminate\Http\Response
     */
    public function updateCantidad(Request $request, $id)
    {
        $nueva = str_replace(',', '.', $request->cantidad);

        if (!is_numeric($nueva) || $nueva <= 0) {
            return response()->json([
                'titulo' => 'Cantidad inválida',
                'message' => 'La cantidad tiene que ser mayor a 0',
            ], 422);
        }

        $subOrder = OrderProduct::findOrFail($id);
        $order = Order::findOrFail($subOrder->id_order);

        if ($order->completada == 1) {
            return response()->json([
                'titulo' => 'Orden cerrada',
                'message' => 'No se puede modificar una venta ya cobrada',
            ], 403);
        }

        $product = Product::findOrFail($subOrder->id_producto);
        $anterior = (float) $subOrder->cantidad;
        $nueva = (float) $nueva;
        $delta = $nueva - $anterior;

        if (abs($delta) < 0.0000001) {
            return response()->json(['message' => 'Sin cambios', 'status' => 'OK'], 200);
        }

        $category = ProductCategory::where('id', $product->id_categoria)->first();
        $unidad = $category ? $category->unidad : '';

        if ($delta > 0 && $product->quedan < $delta) {
            return response()->json([
                'titulo' => 'Imposible vender ' . $nueva . ' ' . $unidad,
                'message' => 'Solo quedan ' . $product->quedan . ' ' . $unidad . ' en STOCK',
            ], 403);
        }

        DB::transaction(function () use ($subOrder, $product, $delta, $anterior, $nueva) {
            $diffMonto = ceil($subOrder->monto * $nueva) - ceil($subOrder->monto * $anterior);

            $subOrder->cantidad = $nueva;
            $subOrder->save();

            if ($delta > 0) {
                $product->decrement('quedan', $delta);
            } else {
                $product->increment('quedan', abs($delta));
            }

            if ($diffMonto > 0) {
                Order::where('id', $subOrder->id_order)->increment('monto', $diffMonto);
            } elseif ($diffMonto < 0) {
                Order::where('id', $subOrder->id_order)->decrement('monto', abs($diffMonto));
            }
        });

        return response()->json([
            'message' => 'Cantidad actualizada: ' . $nueva . ' ' . $unidad . ' de ' . $product->nombre,
            'status' => 'OK',
        ], 200);
    }

    public function delete($id)
    {
        $subOrder = OrderProduct::findOrFail($id);
        $product = Product::findOrFail($subOrder->id_producto);
        
        $category = ProductCategory::where('id', $product->id_categoria)->first();
        $unidad = $category->unidad;

        if ($subOrder->cantidad == 1 && substr($unidad, -1) == "s") //le quito la S a la unidad (Uds => Ud)
        {
            $unidad = substr($unidad, 0, -1);
        }
        
        \DB::table('orders')->where('id', $subOrder->id_order)->decrement('monto', ceil($subOrder->monto * $subOrder->cantidad));
        //\DB::table('orders')->where('id', $subOrder->id_order)->update(['descuento' => 0]);
        \DB::table('products')->where('id', $subOrder->id_producto)->increment('quedan', $subOrder->cantidad);
        
        $subOrder->delete();
        
        return response()->json(["message" => "- " . $subOrder->cantidad . " " . $unidad . " de " . $product->nombre ],200);
    }

    public function delete_svc($id)
    {
        $subOrder = OrdersServices::findOrFail($id);
        

        \DB::table('orders')->where('id', $subOrder->id_order)->decrement('monto', ceil($subOrder->monto));
        
        $subOrder->delete();
        
        return response()->json(["message" => "- " . $subOrder->monto . " de " . $subOrder->description ],200);
    }
}
