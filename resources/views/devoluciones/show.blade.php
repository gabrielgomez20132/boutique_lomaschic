@extends('admin')

@section('content2')
    <div class="d-flex justify-content-between align-items-end">
        <h1 class="mt-2 mb-3">Detalle de Devolución #{{ $devolucion->id }}</h1>
        <p>
            <a href="{{ route('devoluciones.index') }}" class="btn btn-default">
                <i class="glyphicon glyphicon-arrow-left"></i> Volver
            </a>
        </p>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h4>Información General</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Fecha de Devolución:</th>
                                    <td>{{ $devolucion->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Orden Original:</th>
                                    <td>
                                        <a href="{{ route('control.ingresos.productos') }}?order={{ $devolucion->id_order_original }}">
                                            Orden #{{ $devolucion->id_order_original }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $devolucion->cliente->nombre ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Encargado:</th>
                                    <td>{{ $devolucion->encargado->nombre ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Monto Total Devuelto:</th>
                                    <td>
                                        <strong class="text-success" style="font-size: 18px;">
                                            ${{ number_format($devolucion->monto_total, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h4>Vale Generado</h4>
                            @if($devolucion->vale)
                                <div class="panel panel-success">
                                    <div class="panel-heading">
                                        <strong>{{ $devolucion->vale->codigo_vale }}</strong>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table table-condensed" style="margin-bottom: 0;">
                                            <tr>
                                                <th width="180">Monto Original:</th>
                                                <td>${{ number_format($devolucion->vale->monto_original, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Monto Usado:</th>
                                                <td>${{ number_format($devolucion->vale->monto_usado, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Monto Disponible:</th>
                                                <td>
                                                    <strong class="text-success">
                                                        ${{ number_format($devolucion->vale->monto_disponible, 2) }}
                                                    </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Fecha de Emisión:</th>
                                                <td>{{ \Carbon\Carbon::parse($devolucion->vale->fecha_emision)->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha de Vencimiento:</th>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($devolucion->vale->fecha_vencimiento)->format('d/m/Y') }}
                                                    @if($devolucion->vale->isVencido())
                                                        <span class="label label-danger">VENCIDO</span>
                                                    @else
                                                        <span class="label label-success">VIGENTE</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Estado:</th>
                                                <td>
                                                    @if($devolucion->vale->activo)
                                                        <span class="label label-success">ACTIVO</span>
                                                    @else
                                                        <span class="label label-default">INACTIVO</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    No se generó un vale para esta devolución
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h4>Productos Devueltos</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th width="120" class="text-center">Cantidad</th>
                                    <th width="120" class="text-right">Precio Unit.</th>
                                    <th width="120" class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($devolucion->productos as $producto)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $producto->product->nombre ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $producto->cantidad_devuelta }}</td>
                                        <td class="text-right">${{ number_format($producto->precio_unitario, 2) }}</td>
                                        <td class="text-right">
                                            <strong>${{ number_format($producto->subtotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">TOTAL:</th>
                                    <th class="text-right">
                                        <span class="label label-success" style="font-size: 14px; padding: 8px;">
                                            ${{ number_format($devolucion->monto_total, 2) }}
                                        </span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($devolucion->observaciones)
                        <hr>
                        <h4>Observaciones</h4>
                        <div class="well">
                            {{ $devolucion->observaciones }}
                        </div>
                    @endif

                    <div class="form-group" style="margin-top: 30px;">
                        <a href="{{ route('devoluciones.index') }}" class="btn btn-default">
                            <i class="glyphicon glyphicon-arrow-left"></i> Volver al Listado
                        </a>
                        <a href="{{ route('devoluciones.ticket', $devolucion->id) }}" class="btn btn-success" target="_blank">
                            <i class="glyphicon glyphicon-print"></i> Imprimir Vale
                        </a>
                    </div>
                </div>
            </div>

<style>
@media print {
    .btn, .panel-heading .btn, .form-group {
        display: none !important;
    }
}
</style>
@endsection
