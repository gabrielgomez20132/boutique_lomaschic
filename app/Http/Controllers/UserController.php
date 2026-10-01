<?php
//fer
namespace App\Http\Controllers;

use App\User;
use App\UserType;
use Carbon;

use Illuminate\Http\Request;
use App\Deuda;
use App\Vale;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type)
    {
        $id_uType = UserType::where('nombre', $type)->first()->id;
        $users = User::where([['id', '!=', 1], ['id', '!=', 2], ['activo', 1],['id_uType', $id_uType]])->orderBy('nombre')->get();
        $deudas = Deuda::all();
        $activos = true;
        return view('users.index', compact('users','deudas', 'type', 'activos'));
    }

    public function getClientes()
    {
        $clientes = \DB::table('users')->select('id', 'nombre')->where([['id_uType', 2], ['activo', 1]])->orderBy('nombre')->get();
        //$clientes = User::orderBy('id', 'DESC')->get();
        return $clientes;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($type)
    {
        //$type_id = \DB::table('user_types')->where('nombre', $type)->value('id');
        $userType = UserType::where('nombre', $type)->first();
        
        return view('users.create', compact('type','userType'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_uType = $request->id_uType;
        
        if ($id_uType == 2) 
        {
            User::create([
                'nombre' => $request['nombre'],
                'dni_cliente' => $request['dni_cliente'],
                'telefono_cliente' => $request['telefono_cliente'],
                'id_uType' => $request['id_uType']
            ]);
        }
        else 
        {
            User::create([
                'nombre' => $request['nombre'],
                'dni' => $request['dni'],
                'id_uType' => $request['id_uType'],
                'password' => bcrypt($request['password'])
            ]);
        }

        $userType = UserType::find($id_uType);
        $type = $userType->nombre;

        return redirect()->route('users.index', compact('type'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($type, $nombre)
    {
        $user = User::where('nombre', $nombre)->first();
        //dd($type);
        if ($user == null) 
        {
            return view('errors.404');
        }

        return view('users.show', compact('user', 'type'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($type, $id)
    {
        $user = User::find($id);
        return view('users.edit', compact('user', 'type'));
    }

    public function update($type, User $user)
    {
        if($user->id_uType == 1)
        {
            $data = request()->validate([
                'nombre' => 'required',
                'password' => 'required'
            ]);

            $oldPass = $user->password;
        
            if ($oldPass != $data['password']) {
                $data['password'] = bcrypt($data['password']);
            }
        }
        else {
            $data = request()->validate([
                'nombre' => 'required',
                'dni_cliente' => 'nullable',
                'telefono_cliente' => 'nullable'
            ]);
        }
        
        $user->update($data);
        
        return redirect()->route('users.index', compact('type'));
    }

    public function delete($type, User $user)
    {
        $user->update(['activo' => 0]);
        return redirect()->route('users.index', compact('type'));
    }

    public function resurrect($type, User $user)
    {
        $user->update(['activo' => 1]);
        return redirect()->route('users.index', compact('type'));
    }

    public function papelera($type)
    {
        $id_uType = UserType::where('nombre', $type)->first()->id;
        $users = User::where('id', '!=', 1)->where('activo', 0)->where('id_uType', $id_uType)->orderBy('nombre')->get();
        $deudas = Deuda::all();
        $activos = false;
        //dd($users);
        return view('users.index', compact('users', 'deudas', 'type', 'activos'));
    }

    public function record($id)
    {
        $user = User::find($id);
        $nombre = $user->nombre;
        $desde = Carbon::today()->subDays(60);
        $hasta = Carbon::today()->addDays(1);

        if ($user != null && \Request::is('*/clientes/*')) 
        {
            $orders = \DB::table('orders')
                        ->join('users','orders.id_encargado','=','users.id')
                        ->join('formas_pago','orders.id_forma_pago','=','formas_pago.id')
                        ->select('orders.id','users.nombre as encargado','formas_pago.nombre as forma_pago','orders.monto','orders.descuento','orders.fiado','orders.created_at')
                        ->where('orders.id_cliente', $id)
                        ->whereBetween('orders.created_at', [$desde, $hasta])
                        ->orderBy('orders.created_at', 'desc')
                        ->get();

            $deudas = \DB::table('deudas')
                    ->join('users','deudas.id_encargado','=','users.id')
                    ->select('deudas.id','users.nombre as encargado','deudas.tipo','deudas.monto','deudas.created_at')
                    ->where('deudas.id_cliente', $id)
                    ->whereBetween('deudas.created_at', [$desde, $hasta])
                    ->orderBy('deudas.created_at', 'desc')
                    ->get();
            
            $titulo = "Historial para " . $nombre;
            $subtitulo = "Mostrando los últimos 60 días";
            return view('users.record', compact('nombre', 'titulo', 'subtitulo', 'orders', 'deudas', 'orders', 'id'));
        }
    }

    public function historial_record(Request $request, $id)
    {
        $desde = $request->desde;
        $hasta = $request->hasta;

        $nombre = User::find($id)->nombre;
        $orders = \DB::table('orders')
                        ->join('users','orders.id_encargado','=','users.id')
                        ->join('formas_pago','orders.id_forma_pago','=','formas_pago.id')
                        ->select('orders.id','users.nombre as encargado','formas_pago.nombre as forma_pago','orders.monto','orders.descuento','orders.fiado','orders.created_at')
                        ->where('orders.id_cliente', $id)
                        ->whereBetween('orders.created_at', [$desde, $hasta])
                        ->orderBy('orders.created_at', 'desc')
                        ->get();

        $deudas = \DB::table('deudas')
                    ->join('users','deudas.id_encargado','=','users.id')
                    ->select('deudas.id','users.nombre as encargado','deudas.tipo','deudas.monto','deudas.created_at')
                    ->where('deudas.id_cliente', $id)
                    ->whereBetween('deudas.created_at', [$desde, $hasta])
                    ->orderBy('deudas.created_at', 'desc')
                    ->get();

        $orderTypes = \DB::table('orders_type')->select('id', 'nombre')->get();
                    
        $desde = date('d/m/y', strtotime($desde));
        $hasta = date('d/m/y', strtotime($hasta));
        $titulo = "Historial para " . $nombre;
        $subtitulo = "Mostrando resultados de búsqueda desde el " . $desde . " hasta el " . $hasta;
        
        return view('users.record', compact('nombre', 'orderTypes', 'titulo', 'subtitulo', 'orders', 'deudas', 'id'));
    }

    /**
     * Obtener vales disponibles de un cliente
     *
     * @param int $id_cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function getValesCliente($id_cliente)
    {
        $vales = Vale::deCliente($id_cliente)
                    ->disponibles()
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->get();

        return response()->json($vales);
    }

    /**
     * Buscar vale por código (acepta búsqueda parcial)
     *
     * @param string $codigo
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarValePorCodigo($codigo)
    {
        // Primero intentar búsqueda exacta
        $vale = Vale::where('codigo_vale', $codigo)
                    ->disponibles()
                    ->first();

        // Si no se encuentra, intentar búsqueda parcial con LIKE
        if (!$vale) {
            $vale = Vale::where('codigo_vale', 'LIKE', '%' . $codigo . '%')
                        ->disponibles()
                        ->first();
        }

        if ($vale) {
            return response()->json($vale);
        }

        return response()->json(null, 404);
    }
}