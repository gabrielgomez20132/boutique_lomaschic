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

    <div class="row">
        <div class="col-md-12">
            @if($activas == true)
                <h1>Talles</h1>
            @else
                <h1>Talles (Papelera)</h1>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if($activas == true)
                <a class="btn btn-success" href="{{route('talles.create')}}">Crear nuevo talle</a>
            @else
                <a class="btn btn-primary" href="{{route('talles.index')}}">Volver al listado</a>
            @endif
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Fecha de creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr @if($category->activa == 0) style="background-color: #ff8e8e;" @endif>
                                <td>{{ $category->id }}</td>
                                <td>{{ $category->nombre }}</td>
                                <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($category->activa == 1)
                                        <div style="display: flex; gap: 5px;">
                                            <a href="{{ route('talles.edit', $category) }}" class="btn btn-primary btn-sm">Editar</a>
                                            <form action="{{ route('talles.delete', $category) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este talle?')">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
                                                <button class="btn btn-danger btn-sm" type="submit">Borrar</button>
                                            </form>
                                        </div>
                                    @else
                                        <form action="{{ route('talles.resurrect', [$category]) }}" method="POST">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button class="btn btn-success btn-sm" type="submit">Recuperar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection