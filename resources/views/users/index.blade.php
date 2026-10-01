@extends('admin')

@section('content2')
    
    <div class="d-flex justify-content-between align-items-end">
        @if($activos == true)
        <h1 class="mt-2 mb-3">Listado de {{ $type }}s</h1>
        @else
        <h1 class="mt-2 mb-3">Listado de {{ $type }}s borrados</h1>
        @endif
        <p>
            @if($activos == true)
            <a href="/admin/{{$type}}s/nuevo" class="btn btn-primary">Nuevo {{ $type }}</a>
            <a href="/admin/{{$type}}s/papelera" class="btn btn-danger"><span class="oi oi-trash"></span>Papelera</a>
            @else
            <a href="/admin/{{$type}}s" class="btn btn-primary">Volver</a>
            @endif
        </p>
    </div>
    
    <table class="table">
        
        <thead class="thead-dark"></thead>
            <tr>
                <th scope="col">Nombre</th>

                @if($type == "cliente")
                <th scope="col">Dni</th>
                <th scope="col">Telefono</th>
                @endif
                @if($type == "cliente")
                <th scope="col">Debe</th>
                <th scope="col">Pagar</th>
                @endif
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        
        <tbody style="font-size: large;">
            @foreach ($users as $user)
                @if($activos == false)
                <tr style="background-color: #ff8e8e;">
                @else
                <tr>
                @endif
                    <td>{{ $user->nombre }}</td>
                    {{-- @if($type != "cliente")
                    <td>{{ $user->direccion }}</td>
                    <td>{{ $user->telefono }}</td>
                    @endif --}}
                    @php
                        $monto = 0
                    @endphp
                    @if($type == "cliente")
                        @foreach($deudas as $deuda)
                            @if ($deuda->id_cliente == $user->id)
                                @if ($deuda->tipo == "D")
                                    @php
                                        $monto -= $deuda->monto
                                    @endphp
                                @else
                                    @php
                                        $monto += $deuda->monto
                                    @endphp
                                @endif
                            @endif
                        @endforeach
                        <td>{{ $user->dni_cliente }}</td>
                        <td>{{ $user->telefono_cliente }}</td>
                        @if ($monto == 0)
                            <td><span class="oi oi-check" style="color: mediumseagreen;font-size: x-large;"></span></td>
                        @elseif ($monto < 0)
                            <td style="color: red; font-weight: bold;"><b>$</b>{{ $monto * -1 }}</td>
                        @else
                            <td style="color: mediumseagreen; font-weight: bold;"><b>$</b>{{ $monto }}</td>
                        @endif
                        
                    @endif
                    @if($user->id_uType == 2)
                        <td>
                            <form method="POST" action="/admin/control/deudas/pagar">
                                {!!csrf_field()!!}
                                @if ($monto == 0)
                                    <input class="btn btn-default" type="number" min="0" name="pagado" style="width: 85px;" disabled>
                                @else
                                    <div class="input-group" style="width: 135px;">
                                        <input class="form-control pd6" type="number" min="0" name="monto" placeholder="PAGAR" style="height: 34px;">
                                        <span class="input-group-btn">
                                            <button class="btn btn-success" type="submit" style="height: 34px;">
                                                <span class="oi oi-check"></span>
                                            </button>
                                        </span>
                                    </div>
                                    <input type="hidden" class="form-control" name="id_encargado" value="{{ Auth::user()->id }}">
                                    <input type="hidden" class="form-control" name="id_cliente" value="{{ $user->id }}">
                                @endif
                            </form>
                        </td>
                    @endif
                    <td>
                        @if($user->activo == 1)
                            <form action="{{ route('users.delete', [$type, $user]) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <a href="{{ route('users.edit', [$type, $id = $user->id]) }}" class="btn btn-warning"><span class="oi oi-pencil"></span></a>
                                <button class="btn btn-danger" type="submit"><span class="oi oi-trash"></span></button>
                                @if($user->id_uType == 2)
                                    <a href="{{ route('users.record', [$id = $user->id]) }}" class="btn btn-info"><span class="oi oi-clock"></span></a>
                                @endif
                            </form>
                        @else
                            <form action="{{ route('users.resurrect', [$type, $user]) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button class="btn btn-danger" type="submit"><span class="oi oi-action-undo"></span> Recuperar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
      </table>
@endsection