@extends('admin')

@section('content2')
    <div class="d-flex justify-content-between align-items-end">
        <h1 class="mt-2 mb-3">Devoluciones</h1>
        <p>
            <a href="{{ route('devoluciones.create') }}" class="btn btn-primary">
                <i class="glyphicon glyphicon-plus"></i> Nueva Devolución
            </a>
        </p>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible">
                            {{ session('warning') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Orden Original</th>
                                    <th>Cliente</th>
                                    <th>Monto Total</th>
                                    <th>Vale Generado</th>
                                    <th>Encargado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devoluciones as $devolucion)
                                    <tr>
                                        <td>{{ $devolucion->id }}</td>
                                        <td>{{ $devolucion->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('control.ingresos.productos') }}?order={{ $devolucion->id_order_original }}">
                                                Orden #{{ $devolucion->id_order_original }}
                                            </a>
                                        </td>
                                        <td>{{ $devolucion->cliente->nombre ?? 'N/A' }}</td>
                                        <td class="text-right">
                                            <strong>${{ number_format($devolucion->monto_total, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($devolucion->vale)
                                                <span class="label label-success">{{ $devolucion->vale->codigo_vale }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $devolucion->encargado->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('devoluciones.show', $devolucion->id) }}" class="btn btn-info btn-xs">
                                                <i class="glyphicon glyphicon-eye-open"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            No hay devoluciones registradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($devoluciones->hasPages())
                        <div class="text-center">
                            {{ $devoluciones->links() }}
                        </div>
                    @endif
                </div>
            </div>
@endsection
