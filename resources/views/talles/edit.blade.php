@extends('admin')

@section('title', 'Editar Talle')
@section('content2')
    <h1 class="mt-5form-group col-md-12">Editar Talle</h1>
    
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
    
    <form method="POST" action="/admin/talles/{{$category->id}}">
        {{method_field('PUT')}}
        {{csrf_field()}}
        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Nombre</label>
                <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $category->nombre) }}" >
            </div>
                
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-success form-control">Editar Talle</button>
            </div>
            
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <a href="/admin/talles" class="btn btn-primary form-control">Volver</a>
            </div>
        </div>
    </form>        
@endsection