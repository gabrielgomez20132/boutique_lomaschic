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
            <h1>Marcas</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a class="btn btn-success" href="/admin/marcas/nueva">Crear nueva marca</a>
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
                        @foreach($marcas as $marca)
                            <tr>
                                <td>{{ $marca->id }}</td>
                                <td>{{ $marca->nombre }}</td>
                                <td>{{ $marca->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="/admin/marcas/{{ $marca->id }}/editar" class="btn btn-primary btn-sm">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection