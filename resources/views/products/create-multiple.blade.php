@extends('admin')

@section('content2')
<style>
    .variante-item {
        transition: all 0.3s ease;
    }

    .variante-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .form-group label {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .card-header h4 {
        margin-bottom: 5px;
    }
</style>

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

<h2 class="form-group col-md-12">Crear Múltiples {{ $type }}s</h2>

@if ($errors->any())
<ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
</ul>
@endif

<form id="form-multiple" method="POST" action="{{ route('products.store.multiple') }}" enctype="multipart/form-data">
    @csrf

    <!-- Datos Base del Producto -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Datos Base del Producto</h4>
            <!--<small class="text-muted">Estos datos serán compartidos por todas las variantes del producto</small>-->
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label> Nombre del Producto</label>
                    <input required maxlength="55" type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Remera Básica">
                </div>

                <div class="form-group col-md-2">
                    <label> Categoría</label>
                    <select class="form-control" name="id_categoria" required>
                        <option value="">Seleccionar...</option>
                        @foreach($categories as $category)
                        <option value="{{$category->id}}" {{ old('id_categoria') == $category->id ? 'selected' : '' }}>
                            {{$category->nombre}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label> Marca</label>
                    <select class="form-control" name="id_marca" required>
                        <option value="">Seleccionar...</option>
                        @foreach($marcas as $marca)
                        <option value="{{$marca->id}}" {{ old('id_marca') == $marca->id ? 'selected' : '' }}>
                            {{$marca->nombre}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label> Costo</label>
                    <input required type="number" step="0.01" min="0" class="form-control" name="costo" value="{{ old('costo', '0.00') }}" placeholder="0.00">
                </div>

                <div class="form-group col-md-2">
                    <label> Precio Venta</label>
                    <input required type="number" step="0.01" min="0" class="form-control" name="monto" value="{{ old('monto', '0.00') }}" placeholder="0.00">
                </div>
            </div>
        </div>
    </div>

    <!-- Variantes del Producto -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Variantes del Producto</h4>
            <!-- <small class="text-muted">Agrega las diferentes combinaciones de talle, color y código que tendrá este producto</small> -->
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-success" onclick="agregarVariante()">
                    <i class="fa fa-plus"></i> Agregar Variante
                </button>
            </div>
            <div id="variantes-container">
                <!-- Las variantes se agregarán aquí dinámicamente -->
            </div>
        </div>
    </div>

    <hr class="my-4">
    <div class="row" style="margin-top: 100px;">
        <div class="col-md-12 text-right">
            <a href="/admin/productos" class="btn btn-secondary mr-3">Cancelar</a>
            <button type="submit" class="btn btn-success btn-md">Crear Productos</button>
        </div>
    </div>
</form>

<script>
    let varianteIndex = 0;

    function agregarVariante() {
        const container = document.getElementById('variantes-container');
        const varianteHtml = `
                <div class="variante-item border rounded p-3 mb-3 bg-light" data-index="${varianteIndex}">
                    <div class="mb-2">
                        <h6 class="text-primary">Variante ${varianteIndex + 1}</h6>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label>Talle</label>
                            <select class="form-control" name="variantes[${varianteIndex}][id_talle]" required>
                                <option value="">Seleccionar...</option>
                                @foreach($talles as $talle)
                                    <option value="{{$talle->id}}">{{$talle->nombre}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label>Color</label>
                            <select class="form-control" name="variantes[${varianteIndex}][id_color]" required>
                                <option value="">Seleccionar...</option>
                                @foreach($colors as $color)
                                    <option value="{{$color->id}}">{{$color->nombre}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label>Código de Barras</label>
                            <input required type="text" class="form-control" name="variantes[${varianteIndex}][codigo]" placeholder="Código único">
                        </div>

                        <div class="form-group col-md-2">
                            <label>Cantidad Ingreso</label>
                            <input required type="number" min="0" class="form-control" name="variantes[${varianteIndex}][ingreso]" value="1">
                        </div>

                        <div class="form-group col-md-2">
                            <label>Cantidad Mínima</label>
                            <input required type="number" min="1" class="form-control" name="variantes[${varianteIndex}][aviso]" value="1">
                        </div>

                        <div class="form-group col-md-1">
                            <label>Imagen</label>
                            <input type="file" class="form-control" name="variantes[${varianteIndex}][archivo]" accept="image/*">
                        </div>

                        <div class="form-group col-md-1 d-flex align-items-end">
                            <label>Eliminar</label>
                            <button type="button" class="btn btn-danger btn-sm form-control" onclick="eliminarVariante(this)" title="Eliminar variante">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        container.insertAdjacentHTML('beforeend', varianteHtml);
        varianteIndex++;
    }

    function eliminarVariante(button) {
        button.closest('.variante-item').remove();
    }

    // Agregar la primera variante al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        agregarVariante();
        
        const form = document.querySelector('#form-multiple');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault(); // Detener envío por defecto
                
                // 1. Obtener todos los inputs de código
                const codeInputs = document.querySelectorAll('input[name$="[codigo]"]');
                const codigos = Array.from(codeInputs)
                                     .map(input => input.value.trim())
                                     .filter(val => val !== '');
                
                // 2. Validación Local: Hay códigos repetidos en este mismo formulario?
                const uniqueCodigos = new Set(codigos);
                if (uniqueCodigos.size !== codigos.length) {
                    alert('Error: Has ingresado el mismo código de barras en más de una variante.\nPor favor, asegúrate de que cada variante tenga un código único antes de crear los productos.');
                    return; // Abortar
                }
                
                // Si no hay códigos por alguna razón, dejar continuar (el backend validará el required)
                if(codigos.length === 0) {
                    this.submit();
                    return;
                }
                
                // 3. Validación Backend: Existe alguno en BD?
                try {
                    const csrfToken = document.querySelector('input[name="_token"]').value;
                    const response = await fetch('{{ route("products.check-codigos") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ codigos: codigos })
                    });
                    
                    const data = await response.json();
                    
                    if (data.existentes && data.existentes.length > 0) {
                        alert('Error en Base de Datos:\n\nLos siguientes productos y códigos de barras ya existen:\n' + data.existentes.join('\n') + '\n\nCorrige las variantes e intenta nuevamente.');
                        return; // Abortar
                    }
                    
                    // 4. Si todo es exitoso, enviar el formulario de forma estándar
                    this.submit();
                    
                } catch (error) {
                    console.error("Error al validar códigos:", error);
                    alert('Ocurrió un error de conexión al verificar los códigos. Intente nuevamente.');
                }
            });
        }
    });
</script>

@endsection