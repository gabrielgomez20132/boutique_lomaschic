@extends('admin')

@section('content2')
    
    <div class="d-flex justify-content-between align-items-end">
        <h1 class="mt-2 mb-3">Listado de Presupuestos</h1>
        @if( $caja_abierta )
        <p>
            <a href="{{route('presupuestos.nuevo')}}" class="btn btn-primary">Nuevo Presupuesto</a>
        </p>
        @endif
    </div>
    
    <table class="table">
        <thead class="thead-dark"></thead>
            <tr>
                <th scope="col">Cliente</th>
                <th class="col">Fecha</th>
                <th class="col">Monto</th>
                <th class="col">Descuento</th>
                <th class="col">Total</th>
                <th class="col">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($presupuestos as $presupuesto)
                @if ($presupuesto->completada == 1)
                <tr>
                @else
                <tr style="background-color: #ff8e8e;">
                @endif
                    <td>{{ $presupuesto->cliente->nombre }}</td>
                    <td>{{ $presupuesto->created_at }}</td>
                    <td>{{ $presupuesto->monto }}</td>
                    <td>{{ $presupuesto->descuento }}</td>
                    <td>{{ $presupuesto->monto - $presupuesto->descuento }}</td>
                    <td>
                    @if ($presupuesto->completada == 1)
                        <a href="{{ route('presupuestos.editar', $presupuesto->id) }}" class="btn btn-success"><span class="oi oi-eye"></span></a>
                    @else
                        <a href="{{ route('presupuestos.editar', $presupuesto->id) }}" class="btn btn-warning"><span class="oi oi-pencil"></span></a>
                    @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
      </table>
@endsection
