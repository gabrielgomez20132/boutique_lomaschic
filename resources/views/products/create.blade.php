@extends('admin')

@section('content2')
    
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <strong>Operación Exitosa!</strong>
            {{ session()->get('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <strong>Error!</strong>
        {{ session()->get('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    <h1 class="form-group col-md-12">Crear {{ $type }}</h1>
    
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" files="true" action="/admin/{{$type}}s" enctype="multipart/form-data">
        {!!csrf_field()!!}
        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Nombre</label>
                <input required maxlength="55" type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" >
            </div>
        </div>

        <div class="form-group col-md-2">
            <label>Categoria</label>
            <select class="form-control" name="id_categoria" value="{{ old('id_categoria') }}">
                @foreach($categories as $category)
                    <option value="{{$category->id}}">{{$category->nombre}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-2">
            <label>Marca</label>
            <select class="form-control" name="id_marca" value="{{ old('id_marca') }}">
                @foreach($marcas as $marca)
                    <option value="{{$marca->id}}">{{$marca->nombre}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-2">
            <label>Talle</label>
            <select class="form-control" name="id_talle" value="{{ old('id_talle') }}">
                @foreach($talles as $talle)
                    <option value="{{$talle->id}}">{{$talle->nombre}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-2">
            <label>Color</label>
            <select class="form-control" name="id_color" value="{{ old('id_color') }}">
                @foreach($colors as $color)
                    <option value="{{$color->id}}">{{$color->nombre}}</option>
                @endforeach
            </select>
        </div>

        

        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Código de barras</label>
                <input required type="text" class="form-control" name="codigo" value="{{ old('codigo') }}" >
            </div>
        </div>

        {{-- <div class="form-row">
            <div class="form-group col-md-1">
                <label>Ideal</label>
                <input required type="number" min="1" class="form-control" name="ideal" value="{{ old('ideal') }}" >
            </div>
        </div> --}}

        <div class="form-row">
            <div class="form-group col-md-1">
                <label>Ingreso</label>
                <input required type="number" min="0" class="form-control" name="pedido" value="1" >
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-1">
                <label>Aviso</label>
                <input required type="number" min="1" class="form-control" name="aviso" value="1" >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Costo</label>
                <input required class="form-control" name="costo" value="{{ old('costo') }}" >
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Venta</label>
                <input required class="form-control" name="monto" value="{{ old('monto') }}" >
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Imagen</label>
                <label class="btn btn-default btn-file col-md-12">
                    Elegir<input type="file" style="display: none;" name="archivo">
                </label>
            </div>
        </div>

        <div class="form-group col-md-2 d-flex align-items-end">
            <!-- <a href="/admin/{{$type}}s" class="btn btn-secondary mr-2">
                ← Volver
            </a> -->
            <br> 
            <button type="submit" class="btn btn-success">
                💾 Guardar
            </button>
        </div>
    </form>
@endsection