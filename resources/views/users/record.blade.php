@extends('admin')

@section('content2')
    <div class="col-md-12">
        <div style="display: flex;">
            <div>
                <h2 style="margin-bottom: auto;">{{ $titulo }}</h2>
            </div>
            <div style="margin-left: auto; margin-top: 21px; min-width: fit-content;">
                @if($subtitulo == "Mostrando los últimos 60 días")
                    <a href="/admin/clientes" class="btn btn-primary">Volver</a>
                @else
                    <a href="{{ URL::previous() }}" class="btn btn-primary">Volver</a>
                @endif
                <button style="margin-left: 4px;" class="btn btn-info" data-toggle="collapse" data-target="#collapseExample2">
                    Búsqueda
                </button>
            </div>
        </div>
        <h4 style="color: grey;">{{ $subtitulo }}</h4>
        <div class="collapse indent" id="collapseExample2">
            <div class="card card-body">
                <form class="form-inline" method="POST" action="{{ url('/admin/clientes/'.$id.'/historial') }}">
                    {!!csrf_field()!!}
                    <div class="form-group">
                        <label>Búsqueda desde el</label>
                        <input required type="date" class="form-control" name="desde" value="{{ date("Y-m-d") }}">
                    </div>
                    <div class="form-group">
                        <label>Hasta el</label>
                        <input required type="date" class="form-control" name="hasta" value="{{ date("Y-m-d", strtotime("+1 day")) }}">
                    </div>
                                
                    <button type="submit" class="btn btn-success">Buscar</button>
                </form>
            </div>
        </div>
        <hr style="margin-bottom: 11px;">
    </div>
    <div class="col-md-7">
        <h1 style="font-size: 30pt; margin-top: auto;">Compras</h1>
    
        <table class="table">
            <thead class="thead-dark"></thead>
                <tr>
                    <th>Atendió</th>
                    <th class="text-center">F. pago</th>
                    <th class="text-center">Monto</th>
                    <th class="text-center">Fiado</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Hora</th>
                    <th class="text-center">Ver</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{$order->encargado}}</td>
                        <td class="text-center">{{$order->forma_pago}}</td>
                        <td class="text-center"><b>$</b>{{ $order->monto - $order->descuento }}</td>
                        <td class="text-center">
                            @if ($order->fiado > 0)
                            <b style="color: red;">$ {{ $order->fiado }}</b>
                            @else
                            <span class="oi oi-check" style="color: mediumseagreen;font-size: large;"></span>
                            @endif
                        </td>
                        <td class="text-center">{{ date('d/m/y', strtotime($order->created_at)) }}</td>
                        <td class="text-center">{{ date('H:i', strtotime($order->created_at)) }} <b>hs</b></td>
                        <td class="text-center"><a href="/admin/control/ingresos/productos/{{ $order->id }}" class="btn btn-success"><span class="oi oi-eye"></span></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-5">
        <h1 style="font-size: 30pt; margin-top: auto;">Pagos y Deudas</h1>
    
        <table class="table">
            <thead class="thead-dark"></thead>
                <tr>
                    <th>Atendió</th>
                    <th class="text-center">Monto</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Hora</th>
                </tr>
            </thead>
            <tbody style="color: white;">
                @foreach ($deudas as $deuda)
                    @if ($deuda->tipo == 'D')
                    <tr style="background: #f30000">
                    @else
                    <tr style="background: #01b901">
                    @endif
                        <td>{{$deuda->encargado}}</td>
                        <td class="text-center"><b>$</b> {{ $deuda->monto }}</td>
                        <td class="text-center">{{ date('d/m/y', strtotime($deuda->created_at)) }}</td>
                        <td class="text-center">{{ date('H:i', strtotime($deuda->created_at)) }} <b>hs</b></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    

    
@endsection