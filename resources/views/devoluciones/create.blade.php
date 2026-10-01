@extends('admin')

@section('content2')
<div id="app-devolucion">
    <div class="d-flex justify-content-between align-items-end">
        <h1 class="mt-2 mb-3">Nueva Devolución</h1>
        <p>
            <a href="{{ route('devoluciones.index') }}" class="btn btn-default">
                <i class="glyphicon glyphicon-arrow-left"></i> Volver
            </a>
        </p>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('devoluciones.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Buscar Orden <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="buscarOrden"
                                        placeholder="Buscar por Nº orden, cliente o fecha..."
                                        @focus="mostrarOrdenes = true"
                                        @input="mostrarOrdenes = true"
                                    >
                                    <div v-if="mostrarOrdenes" class="list-group" style="position: absolute; z-index: 1000; max-height: 300px; overflow-y: auto; width: 94%; background: white; border: 1px solid #ddd;">
                                        <a
                                            href="#"
                                            class="list-group-item list-group-item-action"
                                            v-for="order in ordenesFiltradas"
                                            :key="order.id"
                                            @click.prevent="seleccionarOrden(order)"
                                        >
                                            <strong>Orden #<span v-text="order.id"></span></strong> -
                                            <span v-text="order.cliente_nombre"></span> -
                                            $<span v-text="order.monto_formateado"></span> -
                                            <span v-text="order.fecha"></span>
                                        </a>
                                        <div v-if="ordenesFiltradas.length === 0" class="list-group-item">
                                            <em class="text-muted">No se encontraron órdenes. Total disponibles: <span v-text="ordenes.length"></span></em>
                                        </div>
                                    </div>
                                    <input type="hidden" name="id_order_original" v-bind:value="ordenSeleccionada">
                                </div>
                            </div>

                            <div class="col-md-6" v-if="ordenCargada">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <p class="form-control-static">
                                        <strong v-text="ordenDetalle.cliente ? ordenDetalle.cliente.nombre : 'Cliente General'"></strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="ordenCargada">
                            <hr>
                            <h4>Productos a Devolver</h4>
                            <p class="text-muted">Seleccione los productos y las cantidades que el cliente desea devolver</p>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" @change="seleccionarTodos" v-model="todosSeleccionados">
                                            </th>
                                            <th>Producto</th>
                                            <th width="120" class="text-center">Cantidad Original</th>
                                            <th width="150" class="text-center">Cantidad a Devolver</th>
                                            <th width="120" class="text-right">Precio Unit.</th>
                                            <th width="120" class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(producto, index) in productosOrden" :key="index">
                                            <td class="text-center">
                                                <input type="checkbox" v-model="producto.seleccionado">
                                            </td>
                                            <td v-text="producto.producto.nombre"></td>
                                            <td class="text-center" v-text="producto.cantidad"></td>
                                            <td class="text-center">
                                                <input
                                                    type="number"
                                                    class="form-control input-sm text-center"
                                                    v-model.number="producto.cantidadDevolver"
                                                    :disabled="!producto.seleccionado"
                                                    min="0.01"
                                                    :max="producto.cantidad"
                                                    step="0.01"
                                                    @input="calcularSubtotal(producto)"
                                                >
                                            </td>
                                            <td class="text-right">
                                                $ <span v-text="parseFloat(producto.precio).toFixed(2)"></span>
                                            </td>
                                            <td class="text-right">
                                                <strong>$ <span v-text="producto.subtotalDevolucion.toFixed(2)"></span></strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">TOTAL A DEVOLVER:</th>
                                            <th class="text-right">
                                                <span class="label label-success" style="font-size: 14px; padding: 8px;">
                                                    $ <span v-text="totalDevolucion.toFixed(2)"></span>
                                                </span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <input type="hidden" name="productos" v-bind:value="JSON.stringify(productosParaEnviar)">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Observaciones</label>
                                        <textarea name="observaciones" class="form-control" rows="3" placeholder="Motivo de la devolución, estado de los productos, etc."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="glyphicon glyphicon-info-sign"></i>
                                <strong>Importante:</strong>
                                Los productos devueltos se agregarán automáticamente al inventario y se generará un vale por el monto de <strong>$<span v-text="totalDevolucion.toFixed(2)"></span></strong> válido por 15 días.
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-lg" :disabled="!puedeGuardar">
                                    <i class="glyphicon glyphicon-ok"></i> Procesar Devolución y Generar Vale
                                </button>
                                <a href="{{ route('devoluciones.index') }}" class="btn btn-default btn-lg">
                                    <i class="glyphicon glyphicon-remove"></i> Cancelar
                                </a>
                            </div>
                        </div>

                        <div v-else class="text-center text-muted" style="padding: 60px 0;">
                            <i class="glyphicon glyphicon-search" style="font-size: 48px;"></i>
                            <p style="margin-top: 20px; font-size: 16px;">Seleccione una orden para comenzar</p>
                        </div>
                    </form>
                </div>
            </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script Vue cargando...');
    console.log('Elemento app-devolucion existe?', document.getElementById('app-devolucion'));
    console.log('Vue disponible?', typeof Vue);
    console.log('axios disponible?', typeof axios);

    if (typeof Vue === 'undefined') {
        console.error('Vue.js no está disponible!');
        return;
    }

    new Vue({
        el: '#app-devolucion',
    data: function() {
        return {
            ordenSeleccionada: '',
            ordenCargada: false,
            ordenDetalle: {},
            productosOrden: [],
            todosSeleccionados: false,
            buscarOrden: '',
            mostrarOrdenes: false,
            ordenes: {!! json_encode($ordersData) !!}
        }
    },
    computed: {
        ordenesFiltradas: function() {
            if (!this.buscarOrden || this.buscarOrden.length < 1) {
                return this.ordenes.slice(0, 20); // Mostrar primeras 20 órdenes
            }
            const busqueda = this.buscarOrden.toLowerCase();
            return this.ordenes.filter(order => {
                return order.texto_busqueda.toLowerCase().includes(busqueda);
            }).slice(0, 20);
        },
        totalDevolucion: function() {
            return this.productosOrden.reduce((total, producto) => {
                if (producto.seleccionado) {
                    return total + producto.subtotalDevolucion;
                }
                return total;
            }, 0);
        },
        productosParaEnviar: function() {
            return this.productosOrden
                .filter(p => p.seleccionado && p.cantidadDevolver > 0)
                .map(p => ({
                    id_producto: p.id_producto,
                    cantidad_devuelta: p.cantidadDevolver,
                    precio_unitario: p.precio
                }));
        },
        puedeGuardar: function() {
            return this.productosParaEnviar.length > 0 && this.totalDevolucion > 0;
        }
    },
    methods: {
        seleccionarOrden: function(order) {
            this.ordenSeleccionada = order.id;
            this.buscarOrden = 'Orden #' + order.id + ' - ' + order.cliente_nombre;
            this.mostrarOrdenes = false;
            this.cargarDetallesOrden();
        },
        cargarDetallesOrden: function() {
            if (!this.ordenSeleccionada) {
                this.ordenCargada = false;
                this.ordenDetalle = {};
                this.productosOrden = [];
                return;
            }

            axios.get('/admin/devoluciones/orden/' + this.ordenSeleccionada)
                .then(response => {
                    console.log('Respuesta de la orden:', response.data);
                    this.ordenDetalle = response.data;

                    if (!response.data.productos || response.data.productos.length === 0) {
                        alert('Esta orden no tiene productos asociados');
                        this.ordenCargada = false;
                        return;
                    }

                    this.productosOrden = response.data.productos.map(p => ({
                        id_producto: p.id_producto,
                        producto: p.producto,
                        cantidad: parseFloat(p.cantidad),
                        precio: parseFloat(p.monto || 0),
                        seleccionado: false,
                        cantidadDevolver: 0,
                        subtotalDevolucion: 0
                    }));
                    this.ordenCargada = true;
                    console.log('Productos cargados:', this.productosOrden);
                })
                .catch(error => {
                    console.error('Error al cargar orden:', error);
                    alert('Error al cargar los detalles de la orden: ' + (error.response?.data?.error || error.message));
                });
        },
        seleccionarTodos: function() {
            this.productosOrden.forEach(producto => {
                producto.seleccionado = this.todosSeleccionados;
                if (this.todosSeleccionados) {
                    producto.cantidadDevolver = producto.cantidad;
                } else {
                    producto.cantidadDevolver = 0;
                }
                this.calcularSubtotal(producto);
            });
        },
        calcularSubtotal: function(producto) {
            if (producto.seleccionado && producto.cantidadDevolver > 0) {
                producto.subtotalDevolucion = producto.cantidadDevolver * producto.precio;
            } else {
                producto.subtotalDevolucion = 0;
            }
        }
    },
    mounted: function() {
        console.log('Vue montado. Órdenes disponibles:', this.ordenes.length);
        console.log('Primeras 3 órdenes:', this.ordenes.slice(0, 3));

        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#app-devolucion')) {
                this.mostrarOrdenes = false;
            }
        });
    }
    });
});
</script>
@endsection
