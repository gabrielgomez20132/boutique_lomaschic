@extends('control.index')

@section('content3')
    <h1 class="mt-5form-group col-md-12">Editar {{$type}}</h1>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <p>Por favor, corrige los errores debajo</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/admin/control/caja/{{$caja->id}}">
        {{method_field('PUT')}}
        {{csrf_field()}}
        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Monto</label>
                <input type="text" class="form-control" name="monto" value="{{ old('monto', $caja->monto) }}" >
            </div>
                
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-success form-control">Editar {{$type}}</button>
            </div>
            
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <a href="/admin/control/caja/inicio" class="btn btn-primary form-control">Volver</a>
            </div>
        </div>
    </form>        
@endsection