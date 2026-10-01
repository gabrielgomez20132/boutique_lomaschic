@extends('admin')

@section('title', "Editar {{ $type }}")
@section('content2')
    <div id="producto">

        <div class="d-flex justify-content-between align-items-end">
            <h1 class="mt-2 mb-3">Editar {{ $type }}</h1>
            <p>
                <button class="btn btn-primary" @click="generateBarcode">Generar Codigo Barra</button>
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <p>Por favor, corrige los errores debajo:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf

            <div class="form-row">
                <div class="form-group col-md-2">
                    <label>Nombre</label>
                    <input maxlength="55" type="text" class="form-control" name="nombre"
                        value="{{ old('nombre', $product->nombre) }}">
                </div>

                <div class="form-group col-md-2">
                    <label>Categoría</label>
                    <select class="form-control" name="id_categoria">
                        <option disabled>Elegir categoría</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ $category->id == $product->id_categoria ? 'selected' : '' }}>
                                {{ $category->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Marca</label>
                    <select class="form-control" name="id_marca">
                        <option disabled>Elegir Marca</option>
                        @foreach ($marcas as $marca)
                            <option value="{{ $marca->id }}"
                                {{ $marca->id == $product->id_marca ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Color</label>
                    <select class="form-control" name="id_color">
                        <option disabled>Elegir Color</option>
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}"
                                {{ $color->id == $product->id_color ? 'selected' : '' }}>
                                {{ $color->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Talle</label>
                    <select class="form-control" name="id_talle">
                        <option disabled>Elegir Color</option>
                        @foreach ($talles as $talle)
                            <option value="{{ $color->id }}"
                                {{ $talle->id == $product->id_talle ? 'selected' : '' }}>
                                {{ $talle->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Código de barras</label>
                    <input type="text" class="form-control" name="codigo" v-model="barcode"
                        value="{{ old('codigo', $product->codigo) }}">
                </div>

                <div class="form-group col-md-1">
                    <label>Agregar</label>
                    <input type="number" min="0" class="form-control" name="pedido" value="0">
                </div>

                <input type="hidden" name="quedan" value="{{ old('quedan', $product->quedan) }}">

                <div class="form-group col-md-1">
                    <label>Aviso</label>
                    <input type="number" class="form-control" name="aviso" value="{{ old('aviso', $product->aviso) }}">
                </div>

                <div class="form-group col-md-1">
                    <label>Costo</label>
                    <input class="form-control" name="costo" value="{{ old('costo', $product->costo) }}">
                </div>

                <div class="form-group col-md-1">
                    <label>Venta</label>
                    <input class="form-control" name="monto" value="{{ old('monto', $product->monto) }}">
                </div>

                <div class="form-group col-md-1">
                    <label>Imagen</label>
                    <label class="btn btn-default btn-file col-md-12">
                        Elegir<input type="file" style="display: none;" name="archivo">
                    </label>
                </div>

                <div class="form-group col-md-2">
                    <label style="display: block;">&nbsp;</label>
                    <button type="submit" class="btn btn-success" style="white-space: nowrap;">
                        <span class="oi oi-check" style="display: inline-block; top: 2px;"></span> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="/js/app.js"></script>
    <script>
        window.App = {
            product_id: {{ $product->id }},
            appkey: {{ $appkey }}
        };

        new Vue({
            el: '#producto',
            data: {
                barcode: '{{ $product->codigo }}'
            },
            methods: {
                generateBarcode() {
                    const prefix = App.appkey.toString().padStart(4, '0');
                    this.barcode = prefix + App.product_id.toString().padStart(8, '0');
                }
            }
        });
    </script>
@endsection
