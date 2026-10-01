<?php

namespace App\Http\Controllers;

use App\Afip;
use Illuminate\Http\Request;
use App\Control;
use App\User;
use App\Order;
use App\Deuda;
use App\OrderProduct;
use App\Product;
use App\FormaPago;
use App\Vale;
use App\OrderVale;
use App\CierreCaja;
use Illuminate\Support\Facades\Auth;

class ControlController extends Controller
{
    public function inicio()
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->where('id_desc', 1)->exists();
        
        $controls = Control::where('id_desc', 1)
                    ->where('caja_abierta', 1)
                    ->get();
        $titulo = "Caja inicial";
        
        $userNoVendedor = Auth::user()->role_id != 2;

        return view('control.caja.inicio', compact('controls', 'titulo', 'caja_abierta', 'userNoVendedor'));
    }
  
 	public function editCaja($id)
    {
        $type = "Caja";
        $caja = Control::find($id);
        return view('control.caja.edit', compact('type','caja'));
    }

    public function update(Control $caja)
    {
        $data = request()->validate([
            'monto' => 'required|numeric',
        ]);
        
        $caja->update($data);
        
        return redirect("/admin/control/caja/inicio");
    }

    public function cierre()
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();
        if (!$caja_abierta) {
            return redirect()->route('control.caja.inicio');
        }

        // --- Resumen del turno (antes de cerrar) ---
        $resumen = $this->datosTurno();

        $resumen['apertura'] = Control::where('id_desc', 1)->where('caja_abierta', 1)->min('created_at');
        $resumen['cierre'] = now()->toDateTimeString();
        $resumen['cerrado_por'] = Auth::user()->nombre ?? '';

        $resumen['cant_ventas'] = Order::where('deHoy', 1)->where('completada', 1)->count();

        $resumen['productos'] = \DB::table('orders')
            ->join('orders_products', 'orders_products.id_order', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'orders_products.id_producto')
            ->leftJoin('product_talles', 'product_talles.id', '=', 'products.id_talle')
            ->leftJoin('product_colors', 'product_colors.id', '=', 'products.id_color')
            ->where('orders.deHoy', 1)->where('orders.completada', 1)
            ->groupBy('products.id', 'products.nombre', 'product_talles.nombre', 'product_colors.nombre')
            ->orderByRaw('SUM(orders_products.cantidad) DESC')
            ->select(
                'products.nombre',
                'product_talles.nombre as talle',
                'product_colors.nombre as color',
                \DB::raw('SUM(orders_products.cantidad) as cantidad'),
                \DB::raw('SUM(orders_products.monto * orders_products.cantidad) as total')
            )
            ->get()
            ->map(function ($p) { return (array) $p; })
            ->all();

        $pendientes = Order::where('deHoy', 1)->where('completada', 0)->where('monto', '>', 0);
        $resumen['pendientes_cant'] = (clone $pendientes)->count();
        $resumen['pendientes_monto'] = (clone $pendientes)->sum('monto') + 0;

        $cierreCaja = CierreCaja::create([
            'apertura'       => $resumen['apertura'],
            'cierre'         => $resumen['cierre'],
            'cerrado_por'    => $resumen['cerrado_por'],
            'cant_ventas'    => $resumen['cant_ventas'],
            'total_efectivo' => $resumen['total_efec'],
            'total_turno'    => $resumen['total_efec'] + $resumen['total_tarj'] + $resumen['total_cheque'] + $resumen['total_transf'] + $resumen['total_mp'],
            'resumen'        => $resumen,
        ]);

        // --- Cierre ---
        \DB::table('controls')
            ->where('caja_abierta', 1)
            ->update(['caja_abierta' => 0]);
        
        \DB::table('orders')
        ->where('deHoy', 1)
        ->update(['deHoy' => 0]);
            
        \DB::table('deudas')
        ->where('deHoy', 1)
        ->update(['deHoy' => 0]);

        return redirect()->route('control.caja.cierres.show', $cierreCaja->id);
    }

    public function cierres()
    {
        $cierres = CierreCaja::orderBy('cierre', 'DESC')->paginate(20);
        $titulo = "Cierres de Caja";

        return view('control.caja.cierres', compact('cierres', 'titulo'));
    }

    public function verCierre($id)
    {
        $cierreCaja = CierreCaja::findOrFail($id);

        $datos = $cierreCaja->resumen;
        $datos['id_cierre'] = $cierreCaja->id;

        return view('control.caja.resumen', $datos);
    }

    public function retiros()
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();
        if($caja_abierta)
        {
            $controls = \DB::table('controls')
                    ->where('caja_abierta', 1)
                    ->where('id_desc', '=', 6)
                    ->get();

            $titulo = "Retiros del día";
            return view('control.caja.retiros', compact('controls', 'titulo'));
        }
        else 
        {
            return view('control.cajaCerrada');
        }
    }

    public function historial_retiros(Request $request)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;
        $controls = \DB::table('controls')
                    ->where('id_desc', '=', 6)
                    ->whereBetween('created_at', [$desde, $hasta])
                    ->get();
        
        $desde = date('d/m/y', strtotime($desde));
        $hasta = date('d/m/y', strtotime($hasta));
        $titulo = "Retiros desde el " . $desde . " hasta el " . $hasta;
        
        return view('control.caja.retiros', compact('controls', 'titulo'));
    }

    public function gastos()
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();
        if($caja_abierta)
        {
            if (\Request::is('*/varios')) 
            { 
                $nombre = "varios"; $id_desc = 3;  
            }
            else if(\Request::is('*/servicios'))
            {
                $nombre = "servicios"; $id_desc = 4;
            }
            else if(\Request::is('*/proveedores'))
            {
                $nombre = "proveedores"; $id_desc = 7;
            }
            // else if(\Request::is('*/comida'))
            // {
            //     $nombre = "comida"; $id_desc = 9;
            // }
            // else if(\Request::is('*/contador'))
            // {
            //     $nombre = "contador"; $id_desc = 10;
            // }
            else {}
            
            $controls = \DB::table('controls')
                        ->where('caja_abierta', 1)
                        ->where('id_desc', '=', $id_desc)
                        ->get();
                        
            $titulo = "Gastos de " . $nombre . " del día";
            
            return view('control.gastos.index', compact('controls', 'titulo', 'nombre', 'id_desc'));
        }
        else 
        {
            return view('control.cajaCerrada');
        }
    }

    public function historial_gastos(Request $request)
    {
        if (\Request::is('*/varios'))
        {
            $nombre = "varios"; $id_desc = 3;
        }
        else if(\Request::is('*/servicios'))
        { 
            $nombre = "servicios"; $id_desc = 4; 
        }
        else if(\Request::is('*/proveedores'))
        { 
            $nombre = "proveedores"; $id_desc = 7; 
        }
        // else if(\Request::is('*/comida'))
        // {
        //     $nombre = "comida"; $id_desc = 9;
        // }
        // else if(\Request::is('*/contador'))
        // {
        //     $nombre = "contador"; $id_desc = 10;
        // }
        else {}

        $desde = $request->desde;
        $hasta = $request->hasta;
        $controls = \DB::table('controls')
                    ->where('id_desc', '=', $id_desc)
                    ->whereBetween('created_at', [$desde, $hasta])
                    ->get();
        
        $desde = date('d/m/y', strtotime($desde));
        $hasta = date('d/m/y', strtotime($hasta));
        $titulo = "Gastos de " . $nombre . " desde " . $desde . " hasta " . $hasta;
        
        return view('control.gastos.index', compact('controls', 'titulo', 'nombre'));
    }

    public function ordenes()
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();
        if($caja_abierta)
        {
            // if (\Request::is('*/productos') || \Request::is('*/control')) 
            // { 
                $tipo = "productos";
                $id_type = 1;
            // }
            
            $orders = \DB::table('orders')->where('id_type', $id_type)->where('deHoy', 1)->orderBy('id', 'DESC')->get();
            $encargados = \DB::table('users')->select('id', 'nombre', 'activo')->where([['id_uType', 1], ['id',"!=", 1],])->orderBy('nombre')->get();
            $clientes = \DB::table('users')->select('id', 'nombre', 'activo')->where('id_uType', 2)->orderBy('nombre')->get();
            $formasPago = \DB::table('formas_pago')->select('id', 'nombre')->get();
            $titulo = "Ingresos por " . $tipo . " del día";
            
            return view('control.ingresos.index', compact('titulo', 'tipo', 'encargados', 'clientes', 'formasPago', 'id_type', 'orders'));
        }
        else 
        {
            return view('control.cajaCerrada');
        }
    }

    public function historial_ordenes(Request $request)
    {
        if (\Request::is('*/productos/historial'))
        {
            $tipo = "productos"; $id_type = 1;
        }
        // else if(\Request::is('*/servicios/historial'))
        // { 
        //     $tipo = "servicios"; $id_type = 2; 
        // }
        
        $desde = $request->desde;
        $hasta = $request->hasta;
        $orders = \DB::table('orders')
                    ->where('id_type', '=', $id_type)
                    ->whereBetween('created_at', [$desde, $hasta])
                    ->orderBy('id', 'DESC')
                    ->get();
        //dd($orders);
        $encargados = \DB::table('users')->select('id', 'nombre', 'activo')->where('id_uType', 1)->orderBy('nombre')->get();
        $clientes = \DB::table('users')->select('id', 'nombre')->where('id_uType', 2)->orderBy('nombre')->get();
        $formasPago = \DB::table('formas_pago')->select('id', 'nombre')->get();
            
        $desde = date('d/m/y', strtotime($desde));
        $hasta = date('d/m/y', strtotime($hasta));
        $titulo = "Ingresos desde " . $desde . " hasta " . $hasta;
        
        return view('control.ingresos.index', compact('titulo', 'tipo', 'encargados', 'clientes', 'formasPago', 'id_type', 'orders'));
    }

    public function store_orden(Request $request)
    {
        $caja_abierta = \DB::table('controls')->where('caja_abierta', 1)->exists();
        if(!$caja_abierta)
        {
            return view('control.cajaCerrada');
        }
        $order = Order::create([
            'id_encargado' => $request['id_encargado'],
            'id_cliente' => $request['id_cliente'],
            'id_type' => $request['id_type'],
            'id_forma_pago' => $request['id_forma_pago'],
            'monto' => $request['monto'],
            'pago_efec' => $request['pago_efec'],
            'pago_tarj' => $request['pago_tarj'],
            'pago_cheque' => $request['pago_cheque'],
            'pago_transf' => $request['pago_transf'],
            'pago_dolares' => $request['pago_dolares'],
            'descuento' => $request['descuento'],
            'completada' => $request['completada'],
            'deHoy' => $request['deHoy']
        ]);
        
        $id_order = $order->id;
        
        switch ($request['id_type']) 
        {
            case '1':
                return redirect()->route('control.ingresos.productos.agregar', compact('id_order'));
                break;
            
            default:
                # code...
                break;
        }
    }

    public function subordenes($id_order)
    {
        if (\Request::is('*/productos/*')) 
        { 
            $tipo = "productos";
            $id_type = 1;
        }
        
        $order = Order::find($id_order);
        if ($order!=null) 
        {
            $regAfipFct = Afip::find($order->idAfipFct);
            //dd($regAfipFct->tipoCbteNum);
            $encargado = User::find($order->id_encargado)->nombre;
            $cliente = User::find($order->id_cliente)->nombre;
            $formaPago = FormaPago::find($order->id_forma_pago)->nombre;
            $subtitulo = "Cliente: " . $cliente . " | Atendió: " . $encargado;

            switch( $order->id_forma_pago ) {
                default:
                    $pie = "Forma de pago: " . $formaPago;
                    break;
                case 3:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_efec . " / $" . $order->pago_tarj . " )";
                    break;
                case 8:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_efec . " / $" . $order->pago_transf . " )";
                    break;
                case 9:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_efec . " / $" . $order->pago_cheque . " )";
                    break;
                case 10:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_efec . " / $" . $order->pago_dolares . " )";
                    break;
                case 11:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_transf . " / $" . $order->pago_tarj . " )";
                    break;
                case 12:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_transf . " / $" . $order->pago_cheque . " )";
                    break;
                case 13:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_transf . " / $" . $order->pago_dolares . " )";
                    break;
                case 14:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_tarj . " / $" . $order->pago_cheque . " )";
                    break;
                case 15:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_tarj . " / $" . $order->pago_dolares . " )";
                    break;
                case 16:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_cheque . " / $" . $order->pago_dolares. " )";
                    break;
                case 17:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->fiado .")";
                    break;
                case 18:
                    $pie = "Forma de pago: " . $formaPago . " ( $" . $order->fiado .")";
                    break;
            }

            // Agregar información del vale si se usó
            if ($order->pago_vale > 0) {
                $pie .= " | Vale: $" . $order->pago_vale;
            }
            /*
            if ($order->id_forma_pago != 3) 
            {
                $pie = "Forma de pago: " . $formaPago;
            }
            else 
            {
                $pie = "Forma de pago: " . $formaPago . " ( $" . $order->pago_efec . " / $" . $order->pago_tarj . " )";
            }*/

            $titulo = "Orden #" . $id_order;
        
            return view('control.ingresos.create', compact('titulo', 'subtitulo', 'pie', 'tipo', 'id_type', 'order', 'regAfipFct'));
        }
        else 
        {
            echo "<h1>La orden todava no existe</h1>";
        }
        
        
    }

    public function store_suborden(Request $request, $id_order)
    {
        $codigo = $request->codigo;
        $cant = $request->cantidad;
        
        $product = Product::where('codigo', $codigo)->first();
        if ($product == null) 
        {
            return redirect()->route('control.ingresos.productos.agregar', compact('id_order'))->with('message', 'El producto con el código ' . $codigo  . ' todavía no ha sido cargado.');
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
            
            Order::where('id', $id_order)->increment('monto', $monto * $cant);
            $product->decrement('quedan', $cant);

            return redirect()->route('control.ingresos.productos.agregar', compact('id_order'));
        } 
        else 
        {
            return redirect()->route('control.ingresos.productos.agregar', compact('id_order'))->with('message', 'Quedan sólo ' . $product->quedan . ' unidades');
        }
        
        
    }

    public function descuento_orden(Request $request, $id_order)
    {
        $descuento = $request->descuento;
        $order = Order::find($id_order);
        $monto = $order->monto;
        $descuento = $monto * $descuento /100;
        
        \DB::table('orders')->where('id', $id_order)->update(['descuento' => $descuento]);
        
        if (\Request::is('*/productos/*')) 
        { 
            return redirect()->route('control.ingresos.productos.agregar', compact('id_order'));
        }
    }

    public function cerrar_orden(Request $request, $id_order)
    {
        //dd($request->all());
        $id_cliente = $request->id_cliente;
        $id_forma_pago = $request->id_forma_pago;
        $descuento = isset($request->descuento) ? $request->descuento : 0;
        
        $order = Order::find($id_order);
        $monto = $order->monto;

        if ($id_forma_pago == 1) 
        {
            if ($descuento == null) {
                $descuento = 0;
            }
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_efec' => $monto - $request->fiado - $descuento,
                        'monto' => $monto,
                        'fiado' => $request->fiado,
                        'id_cliente' => $id_cliente,
                        'id_deuda' => $deuda->id,
                        'completada' => 1,
                        'descuento' => $descuento
                    ]
                );
            }
            else 
            {
                \DB::table('orders')
                ->where('id', $id_order)
                ->update([
                    'pago_efec' => $monto - $descuento, 
                    'id_cliente' => $id_cliente,
                    'completada' => 1,
                    'descuento' => $descuento
                    ]
                );
            }
        }
        elseif ($id_forma_pago == 2) 
        {
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                $montoNeto = $monto - $request->fiado - $descuento;

                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_tarj' => $montoNeto,
                        'monto' => $monto,
                        'fiado' => $request->fiado,
                        'id_forma_pago' => $id_forma_pago,
                        'id_cliente' => $id_cliente,
                        'id_deuda' => $deuda->id,
                        'completada' => 1,
                        'descuento' => $descuento
                    ]
                );
            }
            else 
            {
                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_tarj' => $monto - $descuento, 
                        'id_cliente' => $id_cliente,
                        'id_forma_pago' => $id_forma_pago,
                        'descuento' => $descuento,
                        'completada' => 1
                    ]
                );
            }
        }
        elseif ($id_forma_pago == 3)
        {
            $pago_efec = $request->pago_efec;
            $pago_tarj = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_tarj' => $pago_tarj,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_tarj' => $pago_tarj,
                    'completada' => 1,
                    'descuento' => $descuento,
                ]);
            }
        }
        elseif ($id_forma_pago == 5 || $id_forma_pago == 24)
        {
            if ($request->exists('fiado'))
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                $montoNeto = $monto - $request->fiado - $descuento;

                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_transf' => $montoNeto,
                        'monto' => $monto,
                        'fiado' => $request->fiado,
                        'id_forma_pago' => $id_forma_pago,
                        'id_cliente' => $id_cliente,
                        'id_deuda' => $deuda->id,
                        'descuento' => $descuento,
                        'completada' => 1
                    ]
                );
            }
            else
            {
                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_transf' => $monto - $descuento,
                        'id_cliente' => $id_cliente,
                        'id_forma_pago' => $id_forma_pago,
                        'descuento' => $descuento,
                        'completada' => 1
                    ]
                );
            }
        }
        elseif ($id_forma_pago == 6)//Banco Nacion Marcaton es Tarjetas
        {
            if ($request->exists('fiado'))
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                $montoNeto = $monto - $request->fiado - $descuento;

                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_cheque' => $montoNeto,
                        'monto' => $monto,
                        'fiado' => $request->fiado,
                        'id_forma_pago' => $id_forma_pago,
                        'id_cliente' => $id_cliente,
                        'id_deuda' => $deuda->id,
                        'descuento' => $descuento,
                        'completada' => 1
                    ]
                );
            }
            else
            {
                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_tarj' => $monto - $descuento,
                        'id_cliente' => $id_cliente,
                        'id_forma_pago' => $id_forma_pago,
                        'descuento' => $descuento,
                        'completada' => 1
                    ]
                );
            }
        }
        elseif ($id_forma_pago == 7)
        {
            if ($request->exists('fiado'))
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                $montoNeto = $monto - $request->fiado - $descuento;

                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_dolares' => $montoNeto,
                        'monto' => $monto,
                        'fiado' => $request->fiado,
                        'id_forma_pago' => $id_forma_pago,
                        'id_cliente' => $id_cliente,
                        'id_deuda' => $deuda->id,
                        'descuento' => $descuento,
                        'completada' => 1
                    ]
                );
            }
            else
            {
                \DB::table('orders')
                ->where('id', $id_order)
                ->update(['pago_dolares' => $monto - $descuento,
                        'id_cliente' => $id_cliente,
                        'id_forma_pago' => $id_forma_pago,
                        'descuento' => $descuento,
                        'completada' => 1
                    ]
                );
            }
        }
        elseif ($id_forma_pago == 8)
        {
            $pago_efec = $request->pago_efec;
            $pago_transf = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_transf' => $pago_transf,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_transf' => $pago_transf,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 9)
        {
            $pago_efec = $request->pago_efec;
            $pago_cheque = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_cheque' => $pago_cheque,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_cheque' => $pago_cheque,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 10)
        {
            $pago_efec = $request->pago_efec;
            $pago_dolares = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_dolares' => $pago_dolares,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_efec' => $pago_efec, 
                    'pago_dolares' => $pago_dolares,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 11)
        {
            $pago_transf = $request->pago_efec;
            $pago_tarj = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_transf' => $pago_transf, 
                    'pago_tarj' => $pago_tarj,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_transf' => $pago_transf, 
                    'pago_tarj' => $pago_tarj,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 12)
        {
            $pago_transf = $request->pago_efec;
            $pago_cheque = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_transf' => $pago_transf, 
                    'pago_cheque' => $pago_cheque,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_transf' => $pago_transf, 
                    'pago_cheque' => $pago_cheque,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 13)
        {
            $pago_transf = $request->pago_efec;
            $pago_dolares = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_transf' => $pago_transf, 
                    'pago_dolares' => $pago_dolares,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_transf' => $pago_transf, 
                    'pago_dolares' => $pago_dolares,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 14)
        {
            $pago_tarj = $request->pago_efec;
            $pago_cheque = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_tarj' => $pago_tarj, 
                    'pago_cheque' => $pago_cheque,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_tarj' => $pago_tarj, 
                    'pago_cheque' => $pago_cheque,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 15)
        {
            $pago_tarj = $request->pago_efec;
            $pago_ = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_tarj' => $pago_tarj, 
                    'pago_dolares' => $pago_dolares,
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_tarj' => $pago_tarj, 
                    'pago_dolares' => $pago_dolares,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }
        elseif ($id_forma_pago == 16)
        {
            $pago_cheque = $request->pago_efec;
            $pago_dolares = $request->pago_tarj;
            
            if ($request->exists('fiado')) 
            {
                $deuda = Deuda::create([
                    'id_cliente' => $id_cliente,
                    'id_encargado' => $order->id_encargado,
                    'monto' => $request->fiado,
                    'tipo' => 'D'
                ]);

                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'monto' => $monto,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_cheque' => $pago_cheque,
                    'pago_dolares' => $pago_dolares, 
                    'fiado' => $request->fiado,
                    'id_deuda' => $deuda->id,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
            else 
            {
                Order::where('id', $id_order)->update([
                    'id_cliente' => $id_cliente,
                    'id_forma_pago' => $id_forma_pago,
                    'pago_cheque' => $pago_cheque,
                    'pago_dolares' => $pago_dolares,
                    'descuento' => $descuento,
                    'completada' => 1
                ]);
            }
        }        
        elseif ($id_forma_pago == 4)
        {
            $deuda = Deuda::create([
                'id_cliente' => $id_cliente,
                'id_encargado' => $order->id_encargado,
                'monto' => ($monto - $descuento),
                'tipo' => 'D'
            ]);

            Order::where('id', $id_order)->update([
                'id_cliente' => $id_cliente,
                'id_forma_pago' => $id_forma_pago,
                'monto' => $monto,
                'fiado' => ($monto - $descuento),
                'id_deuda' => $deuda->id,
                'descuento' => $descuento,
                'completada' => 1
            ]);
        }
        // Formas de pago con Vale
        elseif ($id_forma_pago == 19) // Solo Vale
        {
            $pago_vale = $request->pago_vale ?? 0;

            Order::where('id', $id_order)->update([
                'id_cliente' => $id_cliente,
                'id_forma_pago' => $id_forma_pago,
                'pago_vale' => $pago_vale,
                'completada' => 1,
                'descuento' => $descuento,
            ]);
        }
        elseif ($id_forma_pago == 20) // Efectivo/Vale
        {
            $pago_efec = $request->pago_efec ?? 0;
            $pago_vale = $request->pago_vale ?? 0;

            Order::where('id', $id_order)->update([
                'id_cliente' => $id_cliente,
                'id_forma_pago' => $id_forma_pago,
                'pago_efec' => $pago_efec,
                'pago_vale' => $pago_vale,
                'completada' => 1,
                'descuento' => $descuento,
            ]);
        }
        elseif ($id_forma_pago == 21) // Tarjeta/Vale
        {
            $pago_tarj = $request->pago_efec ?? 0; // viene como pago_efec pero es tarjeta
            $pago_vale = $request->pago_vale ?? 0;

            Order::where('id', $id_order)->update([
                'id_cliente' => $id_cliente,
                'id_forma_pago' => $id_forma_pago,
                'pago_tarj' => $pago_tarj,
                'pago_vale' => $pago_vale,
                'completada' => 1,
                'descuento' => $descuento,
            ]);
        }
        elseif ($id_forma_pago == 22) // Transferencia/Vale
        {
            $pago_transf = $request->pago_efec ?? 0; // viene como pago_efec pero es transferencia
            $pago_vale = $request->pago_vale ?? 0;

            Order::where('id', $id_order)->update([
                'id_cliente' => $id_cliente,
                'id_forma_pago' => $id_forma_pago,
                'pago_transf' => $pago_transf,
                'pago_vale' => $pago_vale,
                'completada' => 1,
                'descuento' => $descuento,
            ]);
        }
        elseif ($id_forma_pago == 23) // Cheque/Vale
        {
            $pago_cheque = $request->pago_efec ?? 0; // viene como pago_efec pero es cheque
            $pago_vale = $request->pago_vale ?? 0;

            Order::where('id', $id_order)->update([
                'id_cliente' => $id_cliente,
                'id_forma_pago' => $id_forma_pago,
                'pago_cheque' => $pago_cheque,
                'pago_vale' => $pago_vale,
                'completada' => 1,
                'descuento' => $descuento,
            ]);
        }

        else
        {
            // Cualquier otra forma de pago no contemplada - crear deuda
            $deuda = Deuda::create([
                'id_cliente' => $id_cliente,
                'id_encargado' => $order->id_encargado,
                'monto' => $monto,
                'tipo' => 'D'
            ]);

            Order::where('id', $id_order)->update([
                'id_cliente' => $id_cliente,
                'id_forma_pago' => $id_forma_pago,
                'monto' => $monto,
                'fiado' => $monto,
                'id_deuda' => $deuda->id,
                'descuento' => $descuento,
                'completada' => 1
            ]);
        }

        // Procesar pago con vale (aplicar monto al vale y generar vale nuevo si hay excedente)
        if ($request->has('id_vale') && $request->id_vale) {
            $vale = Vale::find($request->id_vale);
            $monto_vale = $request->pago_vale ?? 0;

            if (!$vale) {
                return back()->withErrors(['error' => 'El vale seleccionado no existe.']);
            }

            if ($monto_vale <= 0) {
                return back()->withErrors(['error' => 'El monto del vale debe ser mayor a cero.']);
            }

            // Verificar que el vale esté activo
            if (!$vale->activo) {
                return back()->withErrors(['error' => 'El vale ya no está activo.']);
            }

            // Verificar que el vale no esté vencido
            if ($vale->isVencido()) {
                return back()->withErrors(['error' => 'El vale está vencido.']);
            }

            // Verificar que el vale tenga saldo suficiente
            if ($vale->monto_disponible < $monto_vale) {
                return back()->withErrors(['error' => 'El vale no tiene saldo suficiente. Disponible: $' . number_format($vale->monto_disponible, 2)]);
            }

            // Aplicar el monto del vale
            if (!$vale->aplicarMonto($monto_vale)) {
                return back()->withErrors(['error' => 'No se pudo aplicar el monto al vale.']);
            }

            // Registrar la relación orden-vale
            OrderVale::create([
                'id_order' => $id_order,
                'id_vale' => $vale->id,
                'monto_aplicado' => $monto_vale
            ]);

            // Verificar si el vale cubrió más que el total (saldo negativo)
            $order = Order::find($id_order);
            $total_pagado = $order->pago_efec + $order->pago_tarj + $order->pago_cheque +
                          $order->pago_transf + $order->pago_dolares + $order->pago_vale;
            $total_orden = $order->monto - $order->descuento;

            if ($total_pagado > $total_orden) {
                // Generar nuevo vale por el excedente
                $excedente = $total_pagado - $total_orden;

                $nuevo_vale = Vale::create([
                    'codigo_vale' => Vale::generarCodigo(),
                    'id_cliente' => $id_cliente,
                    'monto_original' => $excedente,
                    'monto_usado' => 0,
                    'monto_disponible' => $excedente,
                    'fecha_emision' => now(),
                    'fecha_vencimiento' => now()->addDays(15),
                    'id_devolucion' => null,
                    'activo' => true
                ]);

                // Guardar el ID del nuevo vale en sesión para mostrarlo/imprimirlo
                session([
                    'nuevo_vale_id' => $nuevo_vale->id,
                    'nuevo_vale_codigo' => $nuevo_vale->codigo_vale,
                    'nuevo_vale_monto' => $excedente
                ]);
            }
        }

        return back();
        //return redirect()->route('control.ingresos.productos');
    }

    public function store(Request $request)
    {
        Control::create([
            'admin' => $request['admin'],
            'monto' => $request['monto'],
            'id_desc' => $request['id_desc'],
            'detalle' => $request['detalle'],
            'caja_abierta' => $request['caja_abierta']
        ]);
        
        switch ($request['id_desc']) 
        {
            case '1':
                return redirect()->route('control.caja.inicio');
                break;
            
            // case '2':
            //     return redirect()->route('control.comisiones')->with('message', 'La comisión fue pagada correctamente.');
            //     break;
            
            case '3':
                return redirect()->route('control.gastos.varios');
                break;
            
            case '4':
                return redirect()->route('control.gastos.servicios');
                break;
            
            // case '5':
            //     return redirect()->route('control.sueldos')->with('message', 'El sueldo fue pagado correctamente.');
            //     break;
            
            case '6':
                return redirect()->route('control.caja.retiros');
                break;
            
            case '7':
                return redirect()->route('control.gastos.proveedores');
                break;

            // case '8':
            //     return redirect()->route('control.adelantos')->with('message', 'El adelanto fue pagado correctamente.');
            //     break;

            // case '9':
            //     return redirect()->route('control.gastos.comida');
            //     break;
            
            // case '10':
            //     return redirect()->route('control.gastos.contador');
            //     break;
            
            default:
                # code...
                break;
        }
    }

    public function delete($id)
    {
        Control::destroy($id);
        //return redirect()->route('control.caja.inicio');
        return redirect()->back();
    }

    /**
     * Calcula los totales del turno abierto (deHoy = 1 / caja_abierta = 1).
     * Lo usan Movimientos y el Resumen de cierre, asi los numeros coinciden.
     */
    private function datosTurno()
    {
        $caja_inicial = Control::where('id_desc', 1)
                        ->where('caja_abierta', 1)
                        ->value(\DB::raw("sum(monto)")) + 0;

        // $ingXmercaderias = \DB::table('orders_products')
        //                 ->join('orders', 'orders_products.id_order', '=', 'orders.id')
        //                 ->join('products', 'orders_products.id_producto', '=', 'products.id')
        //                 ->where([['deHoy', 1],
        //                         ['completada', 1],
        //                         ['id_forma_pago', '!=', 4],])
        //                 ->value(\DB::raw("sum(round(orders_products.monto * orders_products.cantidad))")) + 0;

        $ganXservicios = \DB::table('orders')
                           ->join('orders_services', 'orders_services.id_order', '=', 'orders.id')
                           ->where('orders.deHoy', '=', 1)->where('completada', '=', 1)
                           ->sum(\DB::raw('orders_services.monto'));

        $calculo = \DB::table('orders')
                           ->join('orders_products', 'orders_products.id_order', '=', 'orders.id')
                           ->where('orders.deHoy', '=', 1)->where('completada', '=', 1)
                           ->sum(\DB::raw('(orders_products.monto - orders_products.costo_x_uni)*orders_products.cantidad'));

        $ganXmercaderias = $calculo;

        $ingXmercaderias = Order::where([['deHoy', 1], ['completada', 1]])
                        ->value(\DB::raw("sum(monto)")) + 0;
        $ingXprod_efec = Order::where('deHoy', 1)
                        ->value(\DB::raw("sum(pago_efec)")) + 0;
        $ingXprod_tarj = Order::where('deHoy', 1)
                        ->value(\DB::raw("sum(pago_tarj)")) + 0;

        $ingXprod_transf = Order::where('deHoy', 1)->where('id_forma_pago', '!=', 24)
                        ->value(\DB::raw("sum(pago_transf)")) + 0;
        $ingXprod_mp = Order::where('deHoy', 1)->where('id_forma_pago', 24)
                        ->value(\DB::raw("sum(pago_transf)")) + 0;
        $ingXprod_bn = Order::where('deHoy', 1)->where('id_forma_pago', 6)
                        ->value(\DB::raw("sum(pago_cheque)")) + 0;
        $ingXprod_cheque = Order::where('deHoy', 1)->where('id_forma_pago', '!=', 6)
                        ->value(\DB::raw("sum(pago_cheque)")) + 0;
        $ingXprod_dolares = Order::where('deHoy', 1)
                        ->value(\DB::raw("sum(pago_dolares)")) + 0;

        $ingXpago_deudas = Deuda::where('deHoy', 1)
                        ->where('tipo', 'P')
                        ->value(\DB::raw("sum(monto)")) + 0;
        $descuentos = \DB::table('orders')
                        ->where([['deHoy', 1], ['completada', 1]])
                        ->value(\DB::raw("sum(descuento)")) + 0;
        $fiado = \DB::table('orders')
                        ->where([['deHoy', 1], ['completada', 1]])
                        ->value(\DB::raw("sum(fiado)")) + 0;

        $gastosVarios = Control::where('caja_abierta', 1)
                        ->where('id_desc', 3)
                        ->value(\DB::raw("sum(monto)")) + 0;
        $gastXserv = Control::where('caja_abierta', 1)
                        ->where('id_desc', 4)
                        ->value(\DB::raw("sum(monto)")) + 0;
        $gastXprov = Control::where('caja_abierta', 1)
                        ->where('id_desc', 7)
                        ->value(\DB::raw("sum(monto)")) + 0;
        $retiros = Control::where('caja_abierta', 1)
                        ->where('id_desc', 6)
                        ->value(\DB::raw("sum(monto)")) + 0;
        $total_efec = $caja_inicial + $ingXprod_efec + $ingXpago_deudas - $gastosVarios - $gastXserv - $gastXprov - $retiros;
        $total_tarj = $ingXprod_tarj;

        $total_transf = $ingXprod_transf;
        $total_mp = $ingXprod_mp;
        $total_bn = $ingXprod_bn;
        $total_cheque = $ingXprod_cheque;
        $total_dolares = $ingXprod_dolares;
        
        return compact(
            'caja_inicial', 'ingXmercaderias', 'ganXmercaderias', 'ganXservicios',
            'ingXprod_efec', 'ingXprod_dolares', 'ingXpago_deudas', 'fiado', 'descuentos',
            'gastosVarios', 'gastXserv', 'gastXprov', 'retiros',
            'total_efec', 'total_tarj', 'total_transf', 'total_mp', 'total_cheque', 'total_dolares'
        );
    }

    public function movimientos()
    {
        $datos = $this->datosTurno();
        $datos['titulo'] = "Movimientos del turno";

        return view('control.movimientos.index', $datos);
    }

    public function historial_movimientos(Request $request)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;
        
        $caja_inicial = Control::where('id_desc', 1)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(monto)")) + 0;
        
        // $ingXmercaderias = \DB::table('orders_products')
        //                 ->join('orders', 'orders_products.id_order', '=', 'orders.id')
        //                 ->join('products', 'orders_products.id_producto', '=', 'products.id')
        //                 ->where([['id_forma_pago', '!=', 4],])
        //                 ->whereBetween('orders.created_at', [$desde, $hasta])
        //                 ->value(\DB::raw("sum(orders_products.monto * orders_products.cantidad)")) + 0;

        $ganXservicios = \DB::table('orders')
                           ->join('orders_services', 'orders_services.id_order', '=', 'orders.id')
                           ->where('completada', '=', 1)->whereBetween('orders.created_at', [$desde, $hasta])
                           ->sum(\DB::raw('orders_services.monto'));


        $ingXmercaderias = Order::where('completada', 1)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(monto)")) + 0;

        $ganXmercaderias = \DB::table('orders')
                           ->join('orders_products', 'orders_products.id_order', '=', 'orders.id')
                           ->where('completada', '=', 1)->whereBetween('orders.created_at', [$desde, $hasta])
                           ->sum(\DB::raw('(orders_products.monto - orders_products.costo_x_uni)*orders_products.cantidad'));

        $ingXprod_efec = Order::where('id_type', 1)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(pago_efec)")) + 0;
        $ingXprod_tarj = Order::where('id_type', 1)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(pago_tarj)")) + 0;
        $ingXpago_deudas = Deuda::where('tipo', 'P')
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(monto)")) + 0;

        $ingXprod_transf = Order::where('id_type', 1)->where('id_forma_pago', '!=', 24)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(pago_transf)")) + 0;
        $ingXprod_mp = Order::where('id_type', 1)->where('id_forma_pago', 24)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(pago_transf)")) + 0;
        $ingXprod_bn = Order::where('id_type', 1)->where('id_forma_pago', 6)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(pago_cheque)")) + 0;
        $ingXprod_cheque = Order::where('id_type', 1)->where('id_forma_pago', '!=', 6)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(pago_cheque)")) + 0;
        $ingXprod_dolares = Order::where('id_type', 1)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(pago_dolares)")) + 0;

        ////////////////////////////////////////////////////////////////////////////VER FIADO
        $fiado = Order::where([['id_type', 1]])
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(fiado)")) + 0;
        $descuentos = Order::where([['id_type', 1]])
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(descuento)")) + 0;
        $gastosVarios = Control::where('id_desc', 3)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(monto)")) + 0;
        $gastXserv = Control::where('id_desc', 4)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(monto)")) + 0;
        $gastXprov = Control::where('id_desc', 7)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(monto)")) + 0;
        $retiros = Control::where('id_desc', 6)
                        ->whereBetween('created_at', [$desde, $hasta])
                        ->value(\DB::raw("sum(monto)")) + 0;
        
        $total_efec = $caja_inicial + $ingXprod_efec + $ingXpago_deudas - $gastosVarios - $gastXserv - $gastXprov - $retiros;
        $total_tarj = $ingXprod_tarj;
                        
        $desde = date('d/m/y', strtotime($request->desde));
        $hasta = date('d/m/y', strtotime($request->hasta));

        $total_transf = $ingXprod_transf;
        $total_mp = $ingXprod_mp;
        $total_bn = $ingXprod_bn;
        $total_cheque = $ingXprod_cheque;
        $total_dolares = $ingXprod_dolares;
        
        $titulo = "Movimientos desde " . $desde . " hasta " . $hasta;
        
        return view('control.movimientos.index', compact(
            'titulo', 
            'caja_inicial', 
            'ingXmercaderias', 
            'ganXmercaderias', 
            'ganXservicios', 
            'ingXpago_deudas', 
            'fiado', 
            'descuentos',
            'gastosVarios', 
            'gastXserv', 
            'gastXprov', 
            'retiros', 
            'total_efec', 
            'total_tarj',
            'total_transf',
            'total_mp',
            'total_bn',
            'total_cheque',
            'total_dolares'
        ));
    }
}
