<?php

namespace App\Http\Controllers;

use App\Classes\WSAA;
use App\Classes\WSFEV1;
use App\Afip;
use App\Order;
use App\Deuda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AfipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $registros = DB::table('afip')->where('pagada', 0)->orderBy('id', 'DESC')->get();
        
        $impTotal = 0;
        $netoTotal = 0;
        $ivaTotal = 0;
        
        foreach ($registros as $registro) 
        {
            if ($registro->tipoCbteNum == 11) //Factura C
            {
                $impTotal = $impTotal + $registro->impTotal;
                $netoTotal = $netoTotal + $registro->impTotal; //netoTotal = $registro->impTotal ya que es exento de IVA
            }
            elseif ($registro->tipoCbteNum == 13) //Nota de Crédito C
            {
                $impTotal = $impTotal - $registro->impTotal;
                $netoTotal = $netoTotal - $registro->impTotal; //netoTotal = $registro->impTotal ya que es exento de IVA
            }
            elseif ($registro->tipoCbteNum == 1 || $registro->tipoCbteNum == 6)
            {
                $impTotal = $impTotal + $registro->impTotal;
                $netoTotal = $netoTotal + $registro->impNeto;
                $ivaTotal = $ivaTotal + $registro->impIVA;
            }
            else //3 u 8
            {
                $impTotal = $impTotal - $registro->impTotal;
                $netoTotal = $netoTotal - $registro->impNeto;
                $ivaTotal = $ivaTotal - $registro->impIVA;
            }
        }
        
        $comprobantes = $registros->count();
        
        return view('afip.index', compact('registros', 'impTotal', 'netoTotal', 'ivaTotal', 'comprobantes'));
    }

    public function generarCbte(Request $request)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        
        $order = Order::find($request->id_order);
        
        $descuento = sprintf("%.2f", $order->descuento);

        if($request->cbte == "FCT" && $order->idAfipFct == 0 || $request->cbte == "NDC" && $order->idAfipNdc == 0)
        {
            $nombreRS = $request->nombreRS;
            
            $_cuit= (float)$request->_cuit;
            if ($_cuit != null) 
            {
                $docTipo = 80;
            } 
            else 
            {
                $docTipo = 99;
                $_cuit = 0;
            }

            $detalleOpcional = $request->detalleOpcional;
            
            $montoOpcional = sprintf("%.2f", $request->montoOpcional);

            $tipoPago = $request->tipoPago;
            
            if ($tipoPago == "Efectivo" ) 
            {
                $tipoPagoSHORT = "EFTV";
            } 
            else 
            {
                $tipoPagoSHORT = "TRJT";
            }
            
            $concepto = $request->concepto;
            
            if ($concepto == "Detalle") {
                $shortCon = "D";
            } elseif($concepto == "Varios") {
                $shortCon = "V";
            } else {
                $shortCon = $concepto;
            }

            $tipoCbte = $request->tipoCbte;
            
            $cbte = $request->cbte;
            if ($cbte == "NDC")
            {
                $impNeto = $request->impNeto;
                $impIVA = $request->impIVA;
                $impTotal = $request->impTotal;

                $facturaAsoc = $request->facturaAsoc;
                $ARfacturaAsoc = explode("-", $facturaAsoc);

                $tipoCbteNom = "NOTA DE CREDITO";
                
                if ($tipoCbte == "A") 
                {
                    $tipoCbteNum = 3;
                    $tipoCbteNumAsoc = 1;
                } 
                else if ($tipoCbte == "B")
                {
                    $tipoCbteNum = 8;
                    $tipoCbteNumAsoc = 6;
                }
                else if ($tipoCbte == "C")
                {
                    $tipoCbteNum = 13;
                    $tipoCbteNumAsoc = 11;
                }
            }
            else 
            {
                $consultaTotal = $order->monto - $descuento;
                
                $orders_products = \DB::table('orders_products')
                                    ->join('products','products.id','=','orders_products.id_producto')
                                    ->select('orders_products.cantidad','products.nombre','orders_products.monto')
                                    ->where('orders_products.id_order', $request->id_order)
                                    ->get();
                $impTotal = sprintf("%.2f", $consultaTotal);

                if ($montoOpcional) 
                {
                    $impTotal = sprintf("%.2f", ($impTotal + $montoOpcional));
                }

                $impIVA = sprintf("%.2f", round($impTotal*21/121, 2));
                $impNeto = sprintf("%.2f", ($impTotal - $impIVA));

                $tipoCbteNom = "FACTURA";
                $facturaAsoc = null;
                
                if ($tipoCbte == "A") 
                {
                    $tipoCbteNum = 1;
                } 
                else if($tipoCbte == "B")
                {
                    $tipoCbteNum = 6;
                }
                else if($tipoCbte == "C")
                {
                    $tipoCbteNum = 11;
                    ////////////////////////Exento de IVA
                    $impIVA = 0;
                    $impNeto = $impTotal;
                }
            }

            /////////////////////////////////////////////////////////////////////////
            $wsaa = new WSAA();
            // Compruebo fecha de exp y si la excede genero nuevo TA
            
            $fecha_ahora = date("Y-m-d H-i-s");
            $fecha_exp_TA = $wsaa->get_expiration();
            
            if ($fecha_exp_TA < $fecha_ahora) 
            {
                if ($wsaa->generar_TA()) 
                {
                    //echo 'Nuevo TA, válido hasta: ' . $fecha_exp_TA . '<br>';
                } 
                else 
                {
                    echo 'Error al obtener TA';
                }
            } 
            else 
            {
                //echo 'TA reutilizado, válido hasta: ' . $fecha_exp_TA . '<br>';
            }

            //Conecto Wsfev1

            $wsfev1 = new WSFEV1();
            
            // Carga el archivo TA.xml
            $wsfev1->openTA();

            $ptovta = 5; //Punto de Venta SIN CEROS ADELANTE!!
            $tipocbte = $tipoCbteNum; // Factura A: 1 --- Factura B: 6 ---- Factura C: 11
            
            $regfe['CbteTipo'] = $tipocbte;
            $regfe['Concepto'] = 1; //Productos: 1 ---- Servicios: 2 ---- Prod y Serv: 3
            $regfe['DocTipo'] = $docTipo; //80=CUIT -- 96 DNI --- 99 general cons final
            $regfe['DocNro'] = $_cuit;  //0 para consumidor final / importe menor a $1000
            $regfe['CbteFch'] = date('Ymd'); 	// fecha emision de factura
            $regfe['ImpNeto'] = $impNeto;		// Imp Neto
            $regfe['ImpTotConc'] = 0;			// no gravado
            $regfe['ImpIVA'] = $impIVA;	// IVA liquidado
            $regfe['ImpTrib'] = 0;			// otros tributos
            $regfe['ImpOpEx'] = 0;			// operacion exentas
            $regfe['ImpTotal'] = $impTotal;		// total de la factura. ImpNeto + ImpTotConc + ImpIVA + ImpTrib + ImpOpEx
            $regfe['FchServDesde'] = null;	// solo concepto 2 o 3
            $regfe['FchServHasta'] = null;	// solo concepto 2 o 3
            $regfe['FchVtoPago'] = null;		// solo concepto 2 o 3
            $regfe['MonId'] = 'PES'; 			// Id de moneda 'PES'
            $regfe['MonCotiz'] = 1;			// Cotizacion moneda. Solo exportacion

            // Comprobantes asociados (solo notas de crédito y débito):
            if ($cbte == "NDC") 
            {
                $regfeasoc['Tipo'] = $tipoCbteNumAsoc; //91; //tipo 91|5			
                $regfeasoc['PtoVta'] = $ARfacturaAsoc[0];//(int)$ARfacturaAsoc[0];
                $regfeasoc['Nro'] = $ARfacturaAsoc[1];//(float)$ARfacturaAsoc[1];	
            }
            else 
            {
                $regfeasoc['Tipo'] = null;      // ESTO LO PUSE EN NULL PARA
                $regfeasoc['PtoVta'] = null;    // QUE DESPUES EN WSFEV1 PODER
                $regfeasoc['Nro'] = null;       // ELIMINAR EL OBJETO CbtesAsoc
            }

            // Detalle de otros tributos
            $regfetrib['Id'] = 1;
            $regfetrib['Desc'] = '';
            $regfetrib['BaseImp'] = 0;
            $regfetrib['Alic'] = 0;
            $regfetrib['Importe'] = 0;

            // Detalle de iva
            if ($tipoCbte == "C")
            {
                $regfeiva['Id'] = null;        // ESTO LO PUSE EN NULL
                $regfeiva['BaseImp'] = null;   // PARA DESPUES EN WSFEV1
                $regfeiva['Importe'] = null;   // ELIMINAR EL OBJETO IVA
            }
            else // tipoCbte == "A" o "B"
            {
                $regfeiva['Id'] = 5;
                $regfeiva['BaseImp'] = $impNeto;
                $regfeiva['Importe'] = $impIVA;
            }
            

            //Pido ultimo numero autorizado
            $nro = $wsfev1->FECompUltimoAutorizado($ptovta, $tipocbte);
            
            if (!is_numeric($nro)) 
            {
                echo "<br>Error al obtener el último número autorizado<br>";
                $nro = 0;
                $nro1 = 0;
                echo "Code: ", $wsfev1->Code, "<br>";
                echo "Msg: ", $wsfev1->Msg, "<br>";
                echo "Obs: ", $wsfev1->ObsCode, "<br>";
                echo "Msg: ", $wsfev1->ObsMsg, "<br>";
            } 
            else 
            {
                //echo "<br>FECompUltimoAutorizado: $nro <br>"; // no es necesario
                $nro1 = $nro + 1;
                $cae = $wsfev1->FECAESolicitar(
                                                $nro1, // ultimo numero de comprobante autorizado mas uno 
                                                $ptovta,  // el punto de venta
                                                $regfe, // los datos a facturar
                                                $regfeasoc,
                                                $regfetrib,
                                                $regfeiva
                                            );
                $caenum = $cae['cae'];
                $caefvt = $cae['fecha_vencimiento'];
                $numero = $nro + 1;
            }

            if ($caenum) 
            {
                $cuit = '20303538950'; //para el codigo de barras
                $comprob = str_pad($tipocbte, 3, "0", STR_PAD_LEFT); //para el codigo de barras
                $punto =  str_pad($ptovta, 5, "0", STR_PAD_LEFT); //para el codigo de barras

                $compNum = $punto . "-" . str_pad($numero, 8, "0", STR_PAD_LEFT);
                // echo "Factura " . $compNum;
                // echo "<br>";
                // echo "Cae: ", $caenum . "  ";
                // echo "Fecha Vto: ", date("d/m/Y", strtotime($caefvt));
                // echo "<br>";
                // echo "Comprobante " . $comprob;
                
                ///////////////////////////////////////////////////////////////////////////
                //////////////// GUARDAMOS EN LA TABLA AFIP TODOS LOS DATOS ///////////////
                ///////////////////////////////////////////////////////////////////////////
                
                $caeFvt = date("Y-m-d", strtotime($caefvt));
                $cbteFch = date("Y-m-d H:i:s");

                if ($tipoCbte == "C") 
                {
                    $impNeto = 0;
                    $impIVA = 0;
                }
                
                $regAfip = Afip::create([
                    'cbteFch' => $cbteFch, 
                    'tipoCbteNum' => $regfe['CbteTipo'], 
                    'nroCbte' => $compNum,
                    'caeNum' => $caenum,
                    'caeFvt' => $caeFvt,
                    'docTipo' => $regfe['DocTipo'],
                    'docNro' => $regfe['DocNro'],
                    'nombreRS' => $nombreRS,
                    'tipoPago' => $tipoPagoSHORT,
                    'impNeto' => $impNeto,
                    'impIVA' => $impIVA,
                    'descuento' => $descuento,
                    'impTotal' => $impTotal,
                    'pagada' => 0,
                    'nroCbteAsoc' => $facturaAsoc,
                    'codigoBarra' => strval($cuit . $comprob . $punto . $caenum . $caefvt),
                    'detalleOpcional' => $detalleOpcional,
                    'montoOpcional' => $montoOpcional,
                    'concepto' => $shortCon
                ]);
                
                if ($cbte == "NDC") 
                {
                    $order->idAfipNdc = $regAfip->id;
                    
                    /////////////////////////////
                    // Ponemos el monto de la  //
                    // Orden en 0 y devolvemos //
                    // el stock a los valores  //
                    // anteriores a la factura //
                    /////////////////////////////
                    
                    $subOrdenes = \DB::table('orders_products')->where('id_order', $order->id)->get();
                        
                    foreach ($subOrdenes as $subOrden)
                    {
                        \DB::table('products')->where('id', $subOrden->id_producto)->increment('quedan', $subOrden->cantidad);
                    }
                    
                    if ($order->id_deuda > 0) {
                        Deuda::destroy($order->id_deuda);
                        $order->id_deuda = 0;
                    }

                    $order->monto = 0;
                    $order->pago_efec = 0;
                    $order->pago_tarj = 0;
                    $order->fiado = 0;
                    $order->descuento = 0;
                }
                else 
                {
                    $order->idAfipFct = $regAfip->id;
                }
                
                $order->save();

                //////////////////////////////////////////////////////////////////////////
                //////////////////// MOSTRAMOS LA VISTA CON LOS DATOS ////////////////////
                //////////////////////////////////////////////////////////////////////////
                return view('afip.generador', compact(
                    'compNum',
                    'tipoCbteNom',
                    'tipoCbte',
                    'cbteFch',
                    'nombreRS',
                    '_cuit',
                    'docTipo',
                    'cbte',
                    'facturaAsoc',
                    'concepto',
                    'orders_products',
                    'detalleOpcional',
                    'montoOpcional',
                    'descuento',
                    'impTotal',
                    'impNeto',
                    'impIVA',
                    'tipoPago',
                    'caenum',
                    'comprob',
                    'punto',
                    'caefvt',
                    'cuit'
                ));
            } 
            else 
            {
                echo "<hr><p><h4>Error al obtener CAE</h4></p>";
                echo "<b>Código ". $wsfev1->Code. ":</b> ". $wsfev1->Msg;
                // echo "Obs: ", $wsfev1->ObsCode, "<br>";
                // echo $wsfev1->ObsMsg, "<br>";
                // echo "Obs2: ", $wsfev1->ObsCode2, "<br>";
                // echo "Msg2: ", $wsfev1->ObsMsg2, "<br>";
                echo "<p><h2>Ha fallado la solicitud</h2></p>";
            }
        }
        else 
        {
            echo "<h1>El comprobante ya fue emitido</h1>";
        }
    }

    public function verTicket($idOrder)
    {
        $order = Order::find($idOrder);

        $orders_svc = \DB::table('orders_services')->select( DB::raw('1'), 'orders_services.description',
                                                'orders_services.monto', DB::raw("'svc' as tipo"))
                     ->where('orders_services.id_order', $idOrder);

                
        $orders_products = \DB::table('orders_products')
                            ->select('orders_products.cantidad','products.nombre','orders_products.monto', DB::raw("'prod' as tipo"))
                            ->join('products','products.id','=','orders_products.id_producto')
                            ->where('orders_products.id_order', $idOrder)
                            ->unionAll($orders_svc)->get();

        $descuento = sprintf("%.2f", $order->descuento);

        $impTotal = sprintf("%.2f", $order->monto - $descuento);

        $cbteFch = $order->created_at;

        $concepto = "Detalle";
        
        $codigoBarra = "null"; //solo para que no recargue la página al imprimir
        //////////////////////////////////////////////////////////////////////////
        //////////////////// MOSTRAMOS LA VISTA CON LOS DATOS ////////////////////
        //////////////////////////////////////////////////////////////////////////
        
        return view('afip.generador', compact(
            'orders_products',
            'idOrder',
            'descuento',
            'impTotal',
            'cbteFch',
            'concepto',
            'codigoBarra'
        ));
    }

    public function verCbte($idAfip)
    {
        if (\Request::is('*/verNotaDeCredito/*')) 
        {
            $order = DB::table('orders')->where('idAfipNdc', $idAfip)->first();
        
            $cbte = "NDC";

            $tipoCbteNom = "NOTA DE CREDITO";
        }
        else 
        {
            $order = DB::table('orders')->where('idAfipFct', $idAfip)->first();
            
            $cbte = "FCT";

            $tipoCbteNom = "FACTURA";
        }
        
        if(!$order)
        {
            $mensaje = "Orden no encontrada";
            dd($mensaje);
        }

        $regAfip = DB::table('afip')->where('id', $idAfip)->first();
        
        $orders_products = DB::table('orders_products')
                                ->select('orders_products.cantidad','products.nombre','orders_products.monto')
                                ->join('products','products.id','=','orders_products.id_producto')
                                ->where('orders_products.id_order', $order->id)
                                ->get();
        
        $compNum = $regAfip->nroCbte;
        $cbteFch = $regAfip->cbteFch;
        
        $descuento = sprintf("%.2f", $regAfip->descuento);

        $impNeto = $regAfip->impNeto;
        $impIVA = $regAfip->impIVA;
        $impTotal = $regAfip->impTotal;

        $nombreRS = $regAfip->nombreRS;

        $codigoBarra = $regAfip->codigoBarra;
        
        $_cuit = $regAfip->docNro;

        $docTipo = $regAfip->docTipo;
        
        $detalleOpcional = $regAfip->detalleOpcional;
        
        $montoOpcional = $regAfip->montoOpcional;

        $facturaAsoc = $regAfip->nroCbteAsoc;

        $tipoCbteNum = $regAfip->tipoCbteNum;

        $caenum = $regAfip->caeNum;

        $caefvt = $regAfip->caeFvt;
        
        if($tipoCbteNum == 1 || $tipoCbteNum == 3)
        {
            $tipoCbte = "A";
        }
        else if($tipoCbteNum == 6 || $tipoCbteNum == 8)
        {
            $tipoCbte = "B";
        }
        else if($tipoCbteNum == 11 || $tipoCbteNum == 13)
        {
            $tipoCbte = "C";
        }
            
        if ($regAfip->tipoPago == "EFTV" ) 
        {
            $tipoPago = "Efectivo";
        } 
        else 
        {
            $tipoPago = "Tarjeta";
        }
        
        $concepto = $regAfip->concepto;
        
        if ($concepto == "D") 
        {
            $concepto = "Detalle";
        } 
        elseif($concepto == "V") 
        {
            $concepto = "Varios";
        }

        //////////////////////////////////////////////////////////////////////////
        //////////////////// MOSTRAMOS LA VISTA CON LOS DATOS ////////////////////
        //////////////////////////////////////////////////////////////////////////
        return view('afip.generador', compact(
            'compNum',
            'tipoCbteNom',
            'tipoCbte',
            'cbteFch',
            'nombreRS',
            '_cuit',
            'docTipo',
            'cbte',
            'facturaAsoc',
            'concepto',
            'orders_products',
            'detalleOpcional',
            'montoOpcional',
            'impTotal',
            'impNeto',
            'impIVA',
            'descuento',
            'tipoPago',
            'caenum',
            'codigoBarra',
            'caefvt'
        ));
    }
    
    public function pagarTodo()
    {
        DB::update('update afip set pagada = 1 where pagada = 0');
        return redirect()->back();
    }

    public function filtrarCbtes(Request $request)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;
        
        $registros = DB::table('afip')->whereBetween('cbteFch', [$desde, $hasta])->orderBy('id', 'DESC')->get();
        
        $impTotal = 0;
        $netoTotal = 0;
        $ivaTotal = 0;
        
        foreach ($registros as $registro) 
        {
            if ($registro->tipoCbteNum == 11) //Factura C
            {
                $impTotal = $impTotal + $registro->impTotal;
                $netoTotal = $netoTotal + $registro->impTotal; //netoTotal = $registro->impTotal ya que es exento de IVA
            }
            elseif ($registro->tipoCbteNum == 13) //Nota de Crédito C
            {
                $impTotal = $impTotal - $registro->impTotal;
                $netoTotal = $netoTotal - $registro->impTotal; //netoTotal = $registro->impTotal ya que es exento de IVA
            }
            elseif ($registro->tipoCbteNum == 1 || $registro->tipoCbteNum == 6)
            {
                $impTotal = $impTotal + $registro->impTotal;
                $netoTotal = $netoTotal + $registro->impNeto;
                $ivaTotal = $ivaTotal + $registro->impIVA;
            }
            else //3 u 8
            {
                $impTotal = $impTotal - $registro->impTotal;
                $netoTotal = $netoTotal - $registro->impNeto;
                $ivaTotal = $ivaTotal - $registro->impIVA;
            }
        }
        
        $comprobantes = $registros->count();
        
        $desde = date('d/m/y', strtotime($desde));
        $hasta = date('d/m/y', strtotime($hasta));
        $titulo = "Comprobantes desde el " . $desde . " hasta el " . $hasta;

        return view('afip.index', compact('registros', 'impTotal', 'netoTotal', 'ivaTotal', 'comprobantes', 'titulo'));
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Afip  $afip
     * @return \Illuminate\Http\Response
     */
    public function show(Afip $afip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Afip  $afip
     * @return \Illuminate\Http\Response
     */
    public function edit(Afip $afip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Afip  $afip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Afip $afip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Afip  $afip
     * @return \Illuminate\Http\Response
     */
    public function destroy(Afip $afip)
    {
        //
    }
}
