<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\ProductCategory;
use App\Presupuesto;
use App\PresupuestoProducto;
use App\PresupuestoServicio;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PresupuestoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();

        if($caja_abierta)
        {
            $presupuestos = Presupuesto::where('control_id', -1)->orderBy('created_at', 'desc')->get();
        } else {
            $presupuestos = array();
        }

//        return view('products.index', compact('products','categories','type'));
        return view('presupuestos.index', compact('presupuestos', 'caja_abierta'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['id_encargado'] = Auth::user()->id;
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();

        if( ! $caja_abierta)
        {
            return abort(500, 'Error caja cerrada');
        }

        $data['id_cliente'] = 2;
        $data['id_type'] = 0;
        $data['monto'] = 0.0;
        $data['descuento'] = 0.0;
        $data['completada'] = false;

        $presupuesto = Presupuesto::create($data);

        return redirect()->route('presupuestos.editar', $presupuesto->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id, Request $request)
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();

        if( ! $caja_abierta)
        {
            return abort(500, 'Error caja cerrada');
        }

        $id_cliente = $request->id_cliente;
        $descuento = $request->descuento;

        $presupuesto = Presupuesto::findOrFail($id);

        $presupuesto->id_cliente=$id_cliente;
        $presupuesto->descuento = $descuento;
        $presupuesto->completada = 1;

        $presupuesto->save();

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $presupuesto = Presupuesto::find($id);

        if( $presupuesto == null ) {
            return view('errors.404');
        }

        $titulo = "Presupuesto #".$id;
        $subtitulo = "Creacion de Presupuesto";

        return view('presupuestos.create', compact('presupuesto', 'titulo', 'subtitulo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Product $product)
    {
    }

    public function createitem_prod($id, Request $request)
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();

        if( ! $caja_abierta)
        {
            return abort(500, 'Error caja cerrada');
        }

        $product_id = $request->product_id;
        $cant = $request->cantidad;

        $product = Product::find($product_id);
        $category = ProductCategory::where('id', $product->id_categoria)->first();
        $unidad = $category->unidad;

        if ($cant == 1 && substr($unidad, -1) == "s") //le quito la S a la unidad (Uds => Ud)
        {
            $unidad = substr($unidad, 0, -1);
        }

         $monto = $product->monto;

         PresupuestoProducto::create([
             'id_presupuesto' => $id,
             'id_producto' => $product->id,
             'cantidad' => $cant,
             'costo_x_uni' => $product->costo,
             'monto' => $monto
         ]);

         Presupuesto::where('id', $id)->increment('monto', $monto * $cant);

         return response()->json(["message" => "+ " . $cant . " " . $unidad . " de " . $product->nombre, "status" => "OK" ],200);
    }

    public function createitem_svc($id, Request $request)
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();

        if( ! $caja_abierta)
        {
            return abort(500, 'Error caja cerrada');
        }

        $description = $request->descripcion;
        $monto = $request->monto;

        PresupuestoServicio::create([
                'id_presupuesto' => $id,
                'description' => $description,
                'monto' => $monto
        ]);

        Presupuesto::where('id', $id)->increment('monto', ceil($monto));

        return response()->json(["message" => "+ sericio de " . $description ],200);
    }

    public function createitem($id, Request $request)
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();

        if( ! $caja_abierta)
        {
            return abort(500, 'Error caja cerrada');
        }

        $codigo = $request->codigo;
        $cant = $request->cantidad;

        $products = Product::where('codigo', $codigo)->orWhere('nombre', 'like', '%'.$codigo.'%')->get();

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

                    PresupuestoProducto::create([
                        'id_presupuesto' => $id,
                        'id_producto' => $product->id,
                        'cantidad' => $cant,
                        'costo_x_uni' => $product->costo,
                        'monto' => $monto
                    ]);

                    Presupuesto::where('id', $id)->increment('monto', $monto * $cant);

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
        return;
    }

    public function getitems($id)
    {
        $servicios = \DB::table('presupuesto_servicios')->select('presupuesto_servicios.id', DB::raw('1'), 'presupuesto_servicios.description',
                     DB::raw("''"), 'presupuesto_servicios.monto', 'presupuesto_servicios.created_at', DB::raw("'sinImagen.png'"), DB::raw("'svc' as tipo"))
                     ->where('presupuesto_servicios.id_presupuesto', $id);

        $items = \DB::table('presupuesto_productos')
        ->select('presupuesto_productos.id','presupuesto_productos.cantidad','products.nombre','product_categories.unidad',
                   'presupuesto_productos.monto','presupuesto_productos.created_at', 'products.archivo', DB::raw("'prod' as tipo") )
        ->join('products','products.id','=','presupuesto_productos.id_producto')
        ->join('product_categories','product_categories.id','=','products.id_categoria')
        ->where('presupuesto_productos.id_presupuesto', $id)
        ->unionAll($servicios)
        ->get();

        return  $items;

    }

    public function deleteitem($id)
    {
        $presupuestoProducto = PresupuestoProducto::findOrFail($id);

        $id_presupuesto = $presupuestoProducto->id_presupuesto;

        $presupuestoProducto->delete();

        Presupuesto::where('id', $id_presupuesto)->decrement('monto', $presupuestoProducto->monto * $presupuestoProducto->cantidad);

        return response()->json(["message" => "Item borrado" ],200);
    }

    public function deleteitem_svc($id)
    {
        $presupuestoServicio = PresupuestoServicio::findOrFail($id);

        $id_presupuesto = $presupuestoServicio->id_presupuesto;

        $presupuestoServicio->delete();

        Presupuesto::where('id', $id_presupuesto)->decrement('monto', ceil($presupuestoServicio->monto));

        return response()->json(["message" => "Item borrado" ],200);
    }

    public function ver_ticket($id)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $servicios = \DB::table('presupuesto_servicios')->select('presupuesto_servicios.id', DB::raw('1'), 'presupuesto_servicios.description',
                     DB::raw("''"), 'presupuesto_servicios.monto', 'presupuesto_servicios.created_at', DB::raw("'sinImagen.png'"), DB::raw("'svc' as tipo"))
                     ->where('presupuesto_servicios.id_presupuesto', $id);

        $items = \DB::table('presupuesto_productos')
        ->select('presupuesto_productos.id','presupuesto_productos.cantidad','products.nombre','product_categories.unidad',
                   'presupuesto_productos.monto','presupuesto_productos.created_at', 'products.archivo', DB::raw("'prod' as tipo") )
        ->join('products','products.id','=','presupuesto_productos.id_producto')
        ->join('product_categories','product_categories.id','=','products.id_categoria')
        ->where('presupuesto_productos.id_presupuesto', $id)
        ->unionAll($servicios)
        ->get();

        $descuento = sprintf("%.2f", $presupuesto->descuento);

        $impTotal = sprintf("%.2f", $presupuesto->monto - $descuento);

        $cbteFch = $presupuesto->created_at;

        $concepto = "Detalle";

        $presupuesto_id = $id;

        return view('presupuestos.ticket', compact(
            'presupuesto_id',
            'items',
            'descuento',
            'impTotal',
            'cbteFch',
            'concepto'
        ));
    }
}
