@extends('control.index')

@section('content3')
    
    <link href="/css/style.css" rel="stylesheet">
    <div style="display: flex">
        
        @if($titulo == "Ingresos por " . $tipo . " del día")
            <h1>{{ $titulo }}</h1>
        @else
            <h1>{{ $titulo }}</h1>
            <a href="/admin/control/ingresos/{{$tipo}}/" class="btn btn-primary botonDerechaInline">Volver</a>
        @endif
        <button class="btn btn-info botonDerechaInline" data-toggle="collapse" data-target="#collapseExample2">
            Historial
        </button>
    </div>

    <div class="collapse indent" id="collapseExample2">
        <div class="card card-body">
            <p>
                <form class="form-inline" method="POST" action="{{ url('/admin/control/ingresos/' . $tipo . '/historial') }}">
                    {!!csrf_field()!!}
                    <div class="form-group">
                        <label> Desde </label>
                        <input required type="date" class="form-control" name="desde" value="{{ date("Y-m-d") }}">
                    </div>
                    <div class="form-group">
                        <label> Hasta </label>
                        <input required type="date" class="form-control" name="hasta" value="{{ date("Y-m-d", strtotime("+1 day")) }}">
                    </div>
                                
                    <button type="submit" class="btn btn-success">Buscar</button>
                    
                </form>
            </p>
        </div>
    </div>
    
    <div class="tableFixHead" style="height: 275px; margin-bottom: 30px;">
        <table class="table table-bordered table-hover text-center" style="font-size: inherit; margin-bottom: 0px;">
            <thead style="text-align-last: center;">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Atendió</th>
                    <th scope="col">F. pago</th>
                    <th scope="col">Monto</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Hora</th>
                @if ($titulo == "Ingresos por " . $tipo . " del día")
                    <th colspan="2" scope="col" style="width: 1%;"></th>
                @else
                    <th colspan="2" scope="col" style="width: 1%;">Ver</th>
                @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr 
                    @if (!$order->completada)
                    style="background-color: lightgray;"
                    @endif
                    >
                        <td><b>{{ $order->id }}</b></td>
                        <td>
                            @foreach($clientes as $cliente)
                                @if($cliente->id == $order->id_cliente)
                                    {{$cliente->nombre}}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach($encargados as $encargado)
                                @if($encargado->id == $order->id_encargado)
                                    {{$encargado->nombre}}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach($formasPago as $formaPago)
                                @if($formaPago->id == $order->id_forma_pago)
                                    {{$formaPago->nombre}}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td><b>$</b> {{ $order->monto - $order->descuento }}</td>
                        <td>{{ date('d/m/y', strtotime($order->created_at)) }}</td>
                        <td>{{ date('H:i', strtotime($order->created_at)) }}</td>
                    @if (!$order->completada && $titulo == "Ingresos por " . $tipo . " del día")
                        <td colspan="1" style="padding: 2px;">
                            <a href="/admin/control/ingresos/{{$tipo}}/{{ $order->id }}" class="btn btn-success"><span class="oi oi-eye"></span></a>
                        </td>
                        <td colspan="1" style="padding: 2px;">
                            <form action="{{ route('order.delete', [$id = $order->id]) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button class="btn btn-danger" type="submit">
                                    <span class="oi oi-trash"></span>
                                </button>
                            </form>
                        </td>
                    @else
                        <td colspan="2" style="padding: 2px;">
                            <a href="/admin/control/ingresos/{{$tipo}}/{{ $order->id }}" class="btn btn-success"><span class="oi oi-eye"></span></a>
                        </td>
                    @endif
                        
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection