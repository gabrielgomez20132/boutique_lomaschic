@extends('admin')

@section('content2')
    
    <h1 class="form-group col-md-12">Agregar {{ $userType->nombre }}</h1>
    
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/admin/{{ $type }}s">
        {!!csrf_field()!!}
        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Nombre</label>
                <input required type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" >
            </div>
            
            @if($userType->nombre == "encargado")
            {{-- <div class="form-group col-md-2">
                <label>Dirección</label>
                <input type="text" class="form-control" name="direccion" value="{{ old('direccion') }}" >
            </div>

            <div class="form-group col-md-2">
                <label>Teléfono</label>
                <input type="number" class="form-control" name="telefono" placeholder="Sin 0 ni 15" value="{{ old('telefono') }}" >
            </div> --}}
            
            <div class="form-group col-md-2">
                <label>DNI</label>
                <input type="text" class="form-control" name="dni" placeholder="Sin puntos o comas" value="{{ old('dni') }}" onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
            </div>
            
            {{-- <div class="form-group col-md-2">
                <label>Fecha de nac.</label>
                <input type="date" class="form-control" name="nacimiento" value="{{ old('nacimiento') }}" >
            </div> --}}
            
            <div class="form-group col-md-2">
                <label>Contraseña</label>
                <input required type="password" class="form-control" name="password" value="{{ old('password') }}" >
            </div>
            @else
                <div class="form-group col-md-2">
                    <label>Dni Cliente</label>
                    <input required type="text" class="form-control" name="dni_cliente" placeholder="Sin puntos o comas" value="{{ old('dni_cliente') }}">
                </div>

                <div class="form-group col-md-2">
                <label>Telefono Cliente</label>
                <input required type="text" class="form-control" name="telefono_cliente" value="{{ old('telefono_cliente') }}" >
            </div>
            @endif
        </div>

        <div class="form-row">
            <input type="hidden" name="id_uType" value="{{ $userType->id }}" >
            
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-success pd6 form-control">Agregar</button>
            </div>

            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <a href="/admin/{{ $type }}s" class="btn btn-primary pd6 form-control">Volver</a>
            </div>
        </div>
    </form>
    
@endsection