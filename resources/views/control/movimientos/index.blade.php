@extends('control.index')
    <style>
        .verde{
            background-color: #00cf01;
            color: white;
        }
        .roja{
            background-color: #ce0000;
            color: white;
        }
        .verdeOscuro{
            background-color: #007701;
            color:gray;
        }
        .rojaOscuro{
            background-color: #770000;
            color:gray;
        }
        .celeste{
            background-color: #2196f3;
            color: white;
        }
        .azul{
            background-color: #005cce;
            color: white;
        }
        .azulOscuro{
            background-color: #003575;
            color: gray;
        }
        .fila{
            font-size: large;
        }
        .table > tbody > tr.fila > td{
            padding: 5px;
            padding-left: 10px;
        }
    </style>
@section('content3')
    @if( Auth::user()->role_id == 1  )

    <link href="/css/style.css" rel="stylesheet">
    <div style="display: flex;">
        <h1 class="mt-2 mb-3">{{ $titulo }}</h1>
        @if($titulo == "Movimientos del turno")
            <button class="btn btn-info botonDerechaInline" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                Historial
            </button>
        @else
            <a href="{{ url('/admin/control/movimientos/') }}" class="btn btn-primary botonDerechaInline">Volver</a>
        @endif 
    </div>
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <p>
                <form class="form-inline" method="POST" action="{{ url('/admin/control/movimientos/historial') }}">
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

    <table class="table table-bordered">
        <tbody>
            <tr
                @if($caja_inicial == 0) 
                    class="fila verdeOscuro"
                @else
                    class="fila verde"
                @endif>
                <td>Inicio Caja</td>
                <td>$ {{ $caja_inicial }}</td>
            </tr>
            <tr
                @if($ingXmercaderias == 0) 
                    class="fila verdeOscuro"
                @else
                    class="fila verde"
                @endif >
                <td>Ingresos x venta de mercaderias</td>
                <td>$ {{ $ingXmercaderias }}</td>
            </tr>
            <tr
                @if($ganXmercaderias == 0) 
                    class="fila verdeOscuro"
                @else
                    class="fila verde"
                @endif >
                <td>Ganancias x mercader&iacute;as</td>
                <td>$ {{ $ganXmercaderias }}</td>
            </tr>
            <tr
                @if($ganXservicios == 0) 
                    class="fila verdeOscuro"
                @else
                    class="fila verde"
                @endif >
                <td>Ganancias x Servicios</td>
                <td>$ {{ $ganXservicios }}</td>
            </tr>
            <tr
                @if($ingXpago_deudas == 0) 
                    class="fila verdeOscuro"
                @else
                    class="fila verde"
                @endif >
                <td>Ingresos x pago de deudas (FIADO)</td>
                <td>$ {{ $ingXpago_deudas }}</td>
            </tr>
            <tr
                @if($fiado == 0) 
                    class="fila rojaOscuro"
                @else
                    class="fila roja"
                @endif>
                <td>Fiado</td>
                <td>$ {{ $fiado }}</td>
            </tr>
            <tr
                @if($descuentos == 0) 
                    class="fila rojaOscuro"
                @else
                    class="fila roja"
                @endif>
                <td>Descuentos</td>
                <td>$ {{ $descuentos }}</td>
            </tr>
            <tr
                @if($gastXprov == 0) 
                    class="fila rojaOscuro"
                @else
                    class="fila roja"
                @endif>
                <td>Gastos x proveedores</td>
                <td>$ {{ $gastXprov }}</td>
            </tr>
            <tr
                @if($gastXserv == 0) 
                    class="fila rojaOscuro"
                @else
                    class="fila roja"
                @endif>
                <td>Gastos x servicios</td>
                <td>$ {{ $gastXserv }}</td>
            </tr>
            <tr
                @if($gastosVarios == 0) 
                    class="fila rojaOscuro"
                @else
                    class="fila roja"
                @endif>
                <td>Gastos varios</td>
                <td>$ {{ $gastosVarios }}</td>
            </tr>
            <tr
                @if($retiros == 0) 
                    class="fila rojaOscuro"
                @else
                    class="fila roja"
                @endif>
                <td>Retiros</td>
                <td>$ {{ $retiros }}</td>
            </tr>
            <tr
                @if($total_efec == 0) 
                    class="fila azulOscuro"
                @else
                    class="fila celeste"
                @endif>
                <td>Total en Efectivo</td>
                <td>$ {{ $total_efec }}</td>
            </tr>
            <tr
                @if($total_tarj == 0) 
                    class="fila azulOscuro"
                @else
                    class="fila celeste"
                @endif>
                <td>Total en Tarjetas</td>
                <td>$ {{ $total_tarj }}</td>
            </tr>
            <tr
                @if($total_transf == 0) 
                    class="fila azulOscuro"
                @else
                    class="fila celeste"
                @endif>
                <td>Total en Transferencias</td>
                <td>$ {{ $total_transf }}</td>
            </tr>
            <tr
                @if($total_mp == 0) 
                    class="fila azulOscuro"
                @else
                    class="fila celeste"
                @endif>
                <td>Total en Mercado Pago</td>
                <td>$ {{ $total_mp }}</td>
            </tr>
            <tr
                @if($total_cheque == 0) 
                    class="fila azulOscuro"
                @else
                    class="fila celeste"
                @endif>
                <td>Total en Cheques</td>
                <td>$ {{ $total_cheque }}</td>
            </tr>

            <tr
                @if($total_efec + $total_tarj == 0) 
                    class="fila azulOscuro"
                @else
                    class="fila azul"
                @endif>
                <td>TOTAL</td>
                <td>$ {{ $total_efec + $total_tarj + $total_cheque + $total_transf + $total_mp }}</td>
            </tr>
        </tbody>
    </table>
    @endif 
@endsection
