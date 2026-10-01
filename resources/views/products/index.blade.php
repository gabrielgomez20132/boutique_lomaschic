@extends('admin')
@section('content2')
<div id="productos">
    {{-- !{ $data }! --}}
    @if(session()->has('message'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <strong>Operación Exitosa!</strong>
        {{ session()->get('message') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif


    <div class="d-flex justify-content-between align-items-end">

        <h1 class="mt-2 mb-3">Listado de {{ $type }}s</h1>

        <p>
        <div class="form-inline">
            <div class="form-group">
                <a href="{{route('products.create')}}" class="btn btn-primary">
                    <span class="glyphicon glyphicon-plus"></span>
                    Nuevo {{ $type }}
                </a>
            </div>
            <div class="form-group ml-2">
                <a href="{{route('products.create.multiple')}}" class="btn btn-success">
                    <span class="glyphicon glyphicon-duplicate"></span>
                    Múltiples {{ $type }}s
                </a>
            </div>
            <div class="form-group">
                <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                    <span class="glyphicon glyphicon-filter"></span> <b> Filtrar</b>
                </button>
            </div>
            {{-- @if (Auth::user()->id == 1 --}}
            <div class="form-group">
                <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample2">
                    <span class="glyphicon glyphicon-usd"></span> <b> Actualizar</b>
                </button>
            </div>
            {{-- @endif --}}
            <div class="input-group">
                <input type="search" v-model="input" class="form-control" placeholder="Buscar productos...">
                <div class="input-group-addon">
                    <span aria-hidden="true" class="glyphicon glyphicon-search"></span>
                </div>
            </div>
            {{-- <div class="form-group">
                        <form method="GET" action="{{ url('/admin/productos/buscar') }}">
            <input autofocus type="search" name="keyword" class="form-control" placeholder="Buscar productos...">
            <button type="submit" style="display: none;" class="btn btn-primary">Buscar</button>
            </form>
        </div> --}}

        <button class="btn btn-primary mt-3" onclick="generateBarcodes(2)" title="Generar códigos 2 columnas">
            <i class="fas fa-barcode"></i> Generar 2 col
        </button>
        <button class="btn btn-primary mt-3" onclick="generateBarcodes(4)" title="Generar códigos 4 columnas">
            <i class="fas fa-barcode"></i> Generar 4 col
        </button>
    </div>
    </p>
</div>
<div class="collapse" id="collapseExample">
    <div class="card card-body">
        <p>
        <form class="form-inline" method="POST" action="/admin/productos/filtro">
            {!!csrf_field()!!}

            <div class="form-group">
                <select class="form-control" name="id_categoria" value="{{ old('id_categoria') }}">
                    <option value="">Sin categoría</option>
                    @foreach($categories as $category)
                    <option value="{{$category->id}}">{{$category->nombre}}</option>
                    @endforeach
                </select>

                <select class="form-control" name="id_marca" value="{{ old('id_marca') }}">
                    <option value="">Sin marca</option>
                    @foreach($marcas as $marca)
                    <option value="{{$marca->id}}">{{$marca->nombre}}</option>
                    @endforeach
                </select>

                <select class="form-control" name="id_talle" value="{{ old('id_talle') }}">
                    <option value="">Sin talle</option>
                    @foreach($talles as $talle)
                    <option value="{{$talle->id}}">{{$talle->nombre}}</option>
                    @endforeach
                </select>

                <select class="form-control" name="id_color" value="{{ old('id_color') }}">
                    <option value="">Sin color</option>
                    @foreach($colors as $color)
                    <option value="{{$color->id}}">{{$color->nombre}}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success"><span class="oi oi-check"></span></button>
            <a href="{{ route('products.index') }}" class="btn btn-danger"><span class="oi oi-x"></span><b> Borrar filtro</b></a>
        </form>
        </p>
    </div>
</div>
{{-- @if (Auth::user()->id == 1 --}}
<div class="collapse" id="collapseExample2">
    <div class="card card-body">
        <p>
        <form class="form-inline" method="POST" action="/admin/productos/actualizarprecios">
            {!!csrf_field()!!}

            <div class="input-group">
                <div class="input-group-addon">
                    <span aria-hidden="true"><b>+</b></span>
                </div>
                <input style="width: 46px; padding: 6px 0px 6px 6px; text-align: center;" type="number" required min="1" class="form-control" name="porcentaje">
                <div class="input-group-addon">
                    <span aria-hidden="true"><b>%</b></span>
                </div>
            </div>

            <div class="form-group">
                <label style="margin-right: 5px; margin-left: 10px;"><b>Categoría:</b></label>
                <select class="form-control" name="id_categoria">
                    <option selected value=0>Todas las categorías</option>
                    @foreach($categories as $category)
                    <option value="{{$category->id}}">{{$category->nombre}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label style="margin-right: 5px; margin-left: 10px;"><b>Marca:</b></label>
                <select class="form-control" name="id_marca">
                    <option selected value=0>Todas las marcas</option>
                    @foreach($marcas as $marca)
                    <option value="{{$marca->id}}">{{$marca->nombre}}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success"><span class="oi oi-check"></span></button>
        </form>
        </p>
    </div>
</div>
{{-- @endif --}}
<style>
    .table-wrapper {
        overflow-x: auto;
        width: 100%;
        margin: 0 -15px;
        padding: 0 15px;
    }
    .table-wrapper table {
        min-width: 1400px !important;
    }
    .table thead th.sortable {
        cursor: pointer !important;
        user-select: none !important;
        position: relative !important;
        padding-right: 30px !important;
        transition: background-color 0.2s ease !important;
        white-space: nowrap !important;
    }
    .table thead th.sortable:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
        text-decoration: underline;
    }
    .table thead th.sortable:after {
        content: '↕' !important;
        position: absolute !important;
        right: 10px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        opacity: 0.5 !important;
        font-size: 16px !important;
        line-height: 1 !important;
        font-weight: bold !important;
    }
    .table thead th.sortable:hover:after {
        opacity: 1 !important;
    }
    .table thead th.sortable.active-asc {
        background-color: rgba(92, 184, 92, 0.15) !important;
        font-weight: bold !important;
    }
    .table thead th.sortable.active-asc:after {
        content: '▲' !important;
        opacity: 1 !important;
        color: #5cb85c !important;
        font-size: 14px !important;
    }
    .table thead th.sortable.active-desc {
        background-color: rgba(92, 184, 92, 0.15) !important;
        font-weight: bold !important;
    }
    .table thead th.sortable.active-desc:after {
        content: '▼' !important;
        opacity: 1 !important;
        color: #5cb85c !important;
        font-size: 14px !important;
    }
</style>

<div class="table-wrapper">
<table class="table table-sm">
    <thead class="thead-dark">
    <tr>
        <th scope="col"></th>
        <th scope="col" class="sortable {{ $sortBy == 'nombre' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('nombre')">Nombre</th>
        <th scope="col" class="sortable {{ $sortBy == 'id_categoria' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('id_categoria')">Categoría</th>
        <th scope="col" class="sortable {{ $sortBy == 'id_talle' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('id_talle')">Talle</th>
        <th scope="col" class="sortable {{ $sortBy == 'id_color' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('id_color')">Color</th>
        <th scope="col" class="sortable {{ $sortBy == 'codigo' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('codigo')">Código barra</th>
        <th scope="col">Ing.Tot.</th>
        <th scope="col" class="sortable {{ $sortBy == 'pedido' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('pedido')">Ult Ingreso</th>
        <th scope="col" class="sortable {{ $sortBy == 'quedan' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('quedan')">Quedan</th>
        <th scope="col" class="sortable {{ $sortBy == 'costo' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('costo')">Costo</th>
        <th scope="col" class="sortable {{ $sortBy == 'monto' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('monto')">Venta</th>
        <th scope="col" class="sortable {{ $sortBy == 'created_at' ? 'active-' . $sortOrder : '' }}" onclick="sortTable('created_at')">Creado</th>
        <th scope="col">Foto</th>
        <th scope="col">Acciones</th>
    </tr>
    </thead>
    <tbody v-if="keywords == null || keywords == ''">
        @foreach ($products as $product)
            @foreach ($categories as $category)
                @if($product->id_categoria == $category->id)
                    @php
                    $catNombre = $category->nombre;
                    $unidad = $category->unidad;
                    @endphp
                @endif
            @endforeach

            @foreach ($talles as $talle)
                @if($product->id_talle == $talle->id)
                    @php
                    $talleNombre = $talle->nombre;
                    @endphp
                @endif
            @endforeach

            @foreach ($colors as $color)
                @if($product->id_color == $color->id)
                    @php
                    $colorNombre = $color->nombre;
                    @endphp
                @endif
            @endforeach

        @if ($product->quedan > $product->aviso)
        <tr id="product-{{ $product->id }}">
            @else
        <tr id="product-{{ $product->id }}" style="background-color: pink;">
            @endif

            <td>
                <input type="checkbox" class="product-checkbox" value="{{ $product->id }}" data-product-id="{{ $product->id }}">
            </td>
            <td>{{ $product->nombre }}</td>
            <td>
                {{ $catNombre }}
            </td>
            <td>
                {{ $talleNombre }}
            </td>
            <td>
                {{ $colorNombre }}
            </td>
            <td>{{ $product->codigo }}
            </td>
            {{-- <td>{{ $product->ideal }} <b>uds.</b></td> --}}
          	<td>
                {{ $product->getTotalCantidadIngresoProducto()}}
                <b>{{ $unidad }}</b>
            </td>
            <td>
                @if ((int)$product->pedido == $product->pedido)
                {{ $product->pedido }}
                @else
                {{ sprintf("%.3f", $product->pedido) }}
                @endif

                <b>{{ $unidad }}</b>
            </td>
            @if ($product->quedan > $product->aviso)
            <td>
                @if ((int)$product->quedan == $product->quedan)
                {{ $product->quedan }}
                @else
                {{ sprintf("%.3f", $product->quedan) }}
                @endif

                <b>{{ $unidad }}</b>
            </td>
            @else
            <td style="color: red;">
                @if ((int)$product->quedan == $product->quedan)
                {{ $product->quedan }}
                @else
                {{ sprintf("%.3f", $product->quedan) }}
                @endif

                <b>{{ $unidad }}</b>
            </td>
            @endif
            <td><b>$</b> {{ $product->costo }}</td>
            <td><b>$</b> {{ $product->monto }}</td>
            <td style="font-size: 11px;">{{ $product->created_at->format('d/m/Y H:i') }}</td>
            <td>
                <!-- <img class="zoom" width="36px" src="../../uploads/{{$product->archivo}}"> -->
                 <img class="zoom" width="36px" src="{{ asset('storage/products/'.$product->archivo) }}">
            </td>
            <td>
                <div style="display: flex; gap: 1px;">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning"><span class="oi oi-pencil"></span></a>
                    {{-- <a href="javascript:void(0)" class="btn btn-danger" onclick="if(confirm('¿Estás seguro de que deseas eliminar este producto?')) document.getElementById('delete-form-{{ $product->id }}').submit();"><i class="fas fa-trash"></i></a>
                    <form id="delete-form-{{ $product->id }}" action="{{ route('products.delete', $product) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form> --}}
                    <a href="{{ route('products.generate-bar-code', $product) }}" class="btn btn-info"><i class="fas fa-barcode"></i>
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tbody v-else-if="products && products.length > 0">
        <tr v-for="product in products" :key="product.id">
            <td>
                <input type="checkbox" class="product-checkbox" :value="product.id" :data-product-id="product.id">
            </td>
            <td v-html="highlight(product.nombre)"></td>
            <td v-html="highlight(product.category.nombre)"></td>
            <td v-html="highlight(product.talle ? product.talle.nombre : '-')"></td>
            <td v-html="highlight(product.color ? product.color.nombre : '-')"></td>
            <td v-html="highlight(product.codigo)"></td>
          	<td>
                !{product.cantidad_total_ingresada}!<b>uds.</b>
            </td>
            <td > !{product.pedido}!<b>uds.</b></td>
            <td v-if="product.quedan > product.aviso">
                !{ product.quedan }! <b>uds.</b>
            </td>
            <td v-else style="color: red;">
                !{ product.quedan }! <b>uds.</b>
            </td>
            <td><b>$</b> !{ product.costo }!</td>
            <td><b>$</b> !{ product.monto }!</td>
            <td style="font-size: 11px;">!{ product.created_at | formatDate }!</td>
            <td>
                <!-- <img class="zoom" width="36px" :src="'/uploads/' + product.archivo"> -->
                 <img class="zoom" width="36px" :src="'/storage/products/' + product.archivo">
            </td>
            <td>
                <div style="display: flex; gap: 1px;">
                    <a :href="'/admin/productos/' + product.id + '/editar'" class="btn btn-warning">
                        <span class="oi oi-pencil"></span>
                    </a>
                    <!-- <a href="javascript:void(0)" class="btn btn-danger" @click="if(confirm('¿Estás seguro de que deseas eliminar este producto?')) document.getElementById('delete-form-vue-' + product.id).submit();"><i class="fas fa-trash"></i></a>
                    <form :id="'delete-form-vue-' + product.id" :action="'/admin/productos/' + product.id" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form> -->
                    <a :href="'/admin/productos/' + product.id + '/codigo-barras'" class="btn btn-info">
                        <i class="fas fa-barcode"></i>
                    </a>
                </div>
            </td>
        </tr>
    </tbody>

</table>
<div v-if="keywords == null || keywords == ''">
    {!! $products->links('pagination::bootstrap-4') !!}
</div>
</div>
</div>
<script>
    function sortTable(column) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');
        const currentOrder = urlParams.get('order') || 'desc';

        let newOrder = 'asc';
        if (currentSort === column && currentOrder === 'asc') {
            newOrder = 'desc';
        }

        window.location.href = '{{ route("products.index") }}?sort=' + column + '&order=' + newOrder;
    }

    function generateBarcodes(columns) {
        const selectedProducts = [];

        document.querySelectorAll('input[type="checkbox"]:checked').forEach((checkbox) => {
            selectedProducts.push(checkbox.getAttribute('data-product-id'));
        });

        if (selectedProducts.length === 0) {
            alert('Por favor, selecciona al menos un producto.');
            return;
        }

        const params = new URLSearchParams();
        selectedProducts.forEach((id) => params.append('product_ids[]', id));
        params.append('columns', columns);

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `/admin/productos/generate-bar-codes?${params.toString()}`, true);
        xhr.responseType = 'blob';

        xhr.onload = function() {
            if (xhr.status === 200) {
                const blob = xhr.response;
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = 'codigos_de_barra.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            } else {
                console.error('Error al generar los códigos de barras:', xhr.statusText);
            }
        };

        xhr.onerror = function() {
            console.error('Error en la solicitud de generación de códigos de barras');
        };

        xhr.send();
    }

    // Aplicar estilos inline a los elementos sortable
    document.addEventListener('DOMContentLoaded', function() {
        const sortables = document.querySelectorAll('th.sortable');

        sortables.forEach(function(el) {
            // Aplicar estilos inline
            el.style.cursor = 'pointer';
            el.style.position = 'relative';
            el.style.paddingRight = '18px';
            el.style.userSelect = 'none';
            el.style.whiteSpace = 'nowrap';
            el.style.transition = 'all 0.2s ease';

            // Crear el ícono de ordenamiento
            const icon = document.createElement('span');
            icon.style.position = 'absolute';
            icon.style.right = '5px';
            icon.style.top = '50%';
            icon.style.transform = 'translateY(-50%)';
            icon.style.fontSize = '10px';
            icon.style.lineHeight = '1';
            icon.style.display = 'inline-block';
            icon.style.margin = '0';
            icon.style.padding = '0';
            icon.style.verticalAlign = 'middle';
            icon.style.transition = 'all 0.2s ease';

            // Determinar qué ícono mostrar
            if (el.classList.contains('active-asc')) {
                icon.textContent = '▲';
                icon.style.color = '#5cb85c';
                icon.style.opacity = '1';
                el.style.backgroundColor = 'rgba(92, 184, 92, 0.2)';
                el.style.fontWeight = 'bold';
            } else if (el.classList.contains('active-desc')) {
                icon.textContent = '▼';
                icon.style.color = '#5cb85c';
                icon.style.opacity = '1';
                el.style.backgroundColor = 'rgba(92, 184, 92, 0.2)';
                el.style.fontWeight = 'bold';
            } else {
                icon.textContent = '↕';
                icon.style.opacity = '0.3';
                icon.style.color = '#dddddd';
            }

            el.appendChild(icon);

            // Remover listeners previos clonando el elemento
            const newEl = el.cloneNode(true);
            el.parentNode.replaceChild(newEl, el);

            // Obtener referencia al icono en el nuevo elemento
            const newIcon = newEl.querySelector('span');

            // Hover effect más sutil
            newEl.addEventListener('mouseenter', function() {
                // Fondo más claro sutil
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
                if (!this.classList.contains('active-asc') && !this.classList.contains('active-desc')) {
                    newIcon.style.opacity = '1';
                    newIcon.style.color = '#ffffff';
                }
            });

            newEl.addEventListener('mouseleave', function() {
                if (this.classList.contains('active-asc') || this.classList.contains('active-desc')) {
                    this.style.backgroundColor = 'rgba(92, 184, 92, 0.2)';
                } else {
                    this.style.backgroundColor = '';
                    newIcon.style.opacity = '0.3';
                    newIcon.style.color = '#dddddd';
                }
            });
        });
    });
</script>


@endsection