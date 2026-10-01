@extends('admin')

@section('title', 'Editar {{$type}}')
@section('content2')
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
    
    <form method="POST" action="/admin/{{$type}}s/{{$category->id}}">
        {{method_field('PUT')}}
        {{csrf_field()}}
        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Nombre</label>
                <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $category->nombre) }}" >
            </div>
            
            <div class="form-group col-md-2">
                <label>Unidad de medida</label>
                <select required class="form-control" name="unidad">
                    @if($category->unidad == 'Uds')
                        <option selected value="Uds">Unidades</option>
                        <option value="Kg">Kilogramos</option>
                    @elseif($category->unidad == 'Kg')
                        <option value="Uds">Unidades</option>    
                        <option selected value="Kg">Kilogramos</option>
                    @else
                        <option value="" selected disabled hidden>Seleccione</option>
                        <option value="Uds">Unidades</option>
                        <option value="Kg">Kilogramos</option>
                    @endif
                </select>
            </div>
                
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-success form-control">Editar {{$type}}</button>
            </div>
            
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <a href="/admin/{{$type}}s" class="btn btn-primary form-control">Volver</a>
            </div>
        </div>
    </form>        
@endsection