@extends('control.index')

@include('afip.datosFactura')
    
@if ($regAfipFct != null)
    @include('afip.datosNdC')
@endif

@section('content3')
<style>
    #page-wrapper{
        position: relative;
        margin-right: -15px;
    }

    input[type="text"]::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
        color: red;
        opacity: 1; /* Firefox */
    }

    input.input-adic::placeholder {
        color: #1f6fd1;
        opacity: 1;
    }

    .autocomplete-results {
        position: absolute;
        z-index: 1000;
        background: white;
        width: 100%;
        border: 1px solid #ddd;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .autocomplete-result {
        display: block;
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        color: #333 !important;
        text-decoration: none !important;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .autocomplete-result:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }
</style>
    <div id="main" >
        @if(session()->has('message'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <strong>Error!</strong>
                {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
            <div style="display: flex;">
                <div>
                    <h1 style="margin-top: auto;">{{ $titulo }}</h1>
                </div>
                @if($subtitulo != "La orden todavía no existe")
                    <div style="margin-left: auto; margin-top: 3px;">
                        @if($order->completada == 1)
                            @if($order->fiado != 0)
                                <label class="btn btn-danger">FIADO $ {{ number_format($order->fiado, 0, ',', '.') }}</label>
                            @endif
                            
                            @if($order->descuento > 0)
                                <label class="btn btn-danger">DESC. $ {{ number_format($order->descuento, 0, ',', '.') }}</label>
                            @endif

                            @if(($order->recargo ?? 0) > 0)
                                <label class="btn btn-primary">ADIC. $ {{ number_format($order->recargo, 0, ',', '.') }}</label>
                            @endif
                            
                            @if ($order->id_forma_pago != 4)
                                <label class="btn btn-success">PAGADO $ {{ number_format($order->pago_efec + $order->pago_tarj + $order->pago_transf + $order->pago_cheque + $order->pago_dolares + $order->pago_vale, 0, ',', '.') }}</label>
                            @endif
                        @else
                            <button class="btn btn-success">TOTAL $ !{ Number(totalSuma).toLocaleString('es-AR', { maximumFractionDigits: 2 }) }!</button>
                            <template v-if="fdpagoElegida != 4">
                                <input v-if="totalSuma > 0" class="btn btn-default" v-on:click="resetPagocon()" placeholder="$PAGO" min="0" v-model="pagoCon" type="number" style="width: 120px;">
                                <button v-if="vuelto > 0" class="btn btn-primary">VUELTO $ !{ Number(vuelto).toLocaleString('es-AR', { maximumFractionDigits: 2 }) }!</button>   
                            </template>
                        @endif
                    </div>
                    @if($order->completada == 1)
                        <div style="margin-top: 3px; margin-left: 5px;">
                            @if(str_contains(URL::previous(), 'clientes'))
                                <a href="{{ URL::previous() }}" class="btn btn-primary"><b>VOLVER</b></a>
                            @else
                                <a href="/admin/control/ingresos/{{$tipo}}" class="btn btn-primary"><b>VOLVER</b></a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
            <div style="display: flex;">
            <div style="margin-left: auto; margin-top: 3px;width:450px;"></div>
                <div style="margin-left: auto; margin-top: 3px;">
                    @if($order->completada == 1)
                    @else
                    <div v-if="(totalSuma > 0 || descuento > 0)" class="input-group" style="width: 120px;">
                        <div class="input-group-addon" style="font-size: 20px;color: white;background-color: #ff0000;">%</div>
                        <input placeholder="DESC" type="text"class="form-control" v-model="perc_desc" style="color: red; font-weight: bold; padding: 1px; font-size: 20px;text-align: center;" oninput="this.value = this.value.replace(/\D+/g, '')" >
                    </div>
                    @endif
                </div>
                <div style="margin-left: auto; margin-top: 3px;">
                    @if($order->completada == 1)
                    @else
                        <div v-if="(totalSuma > 0 || descuento > 0)" class="input-group" style="width: 120px;">
                            <div class="input-group-addon" style="font-size: 20px;color: white;background-color: #ff0000;">$</div>
                            <input placeholder="DESC"  type="text" v-model="dec_desc" class="form-control" style="color: red; font-weight: bold; padding: 1px; font-size: 20px;text-align: center;" oninput="this.value = this.value.replace(/\D+/g, '')">
                        </div>
                    @endif
                </div>
            </div>
            @if($order->completada != 1)
            {{-- Recargo (% ADIC), justo debajo de % DESC --}}
            <div style="display: flex;">
                <div style="margin-left: auto; margin-top: 3px;width:450px;"></div>
                <div style="margin-left: auto; margin-top: 6px;">
                    <div v-if="(totalSuma > 0 || recargo > 0)" class="input-group" style="width: 120px;">
                        <div class="input-group-addon" style="font-size: 20px;color: white;background-color: #1f6fd1;">%</div>
                        <input placeholder="ADIC" type="text" class="form-control input-adic" v-model="perc_adic" style="color: #1f6fd1; font-weight: bold; padding: 1px; font-size: 20px;text-align: center;" oninput="this.value = this.value.replace(/\D+/g, '')">
                    </div>
                </div>
                <div style="margin-left: auto; margin-top: 6px;">
                    <div style="width: 120px;"></div>
                </div>
            </div>
            @endif
        @if($order->completada == 1)
            <p>
                <h4 class="mt-2 mb-3">{{ $subtitulo }}</h4>
            </p>
            <div style="margin-top: 0px;">
                <h4 style="margin-top: 10px;color: darkviolet;" class="mt-2 mb-3">{{ $pie }}</h4>
                @if($order->fiado != 0)
                <h4 style="margin-top: 10px;color: red;" class="mt-2 mb-3">Quedó debiendo ${{ $order->fiado }}</h4>
                @endif
            </div>
        @endif
        @if($subtitulo != "La orden todavía no existe")
            <div style="margin-top: 2px; margin-left: auto;">
                @if($order->completada != 1)
                    <div class="form-group col-md-12" style="margin-top: 0px;padding-left: 0px; padding-right: 0px;">
                        <form id="form-cerrar-orden" method="POST" action="/admin/control/{{$tipo}}/cerrar/{{ $order->id }}">
                            {!!csrf_field()!!}
                            
                            <div v-if="clienteElegido == 2" class="form-group col-md-2" style="padding-left: 0px;">
                                <label>Cliente</label>
                                <select v-model="clienteElegido" class="form-control pd6" name="id_cliente" value="{{ old('id_cliente') }}">
                                    <option v-for="cliente in clientes" v-bind:value="cliente.id">!{ cliente.nombre }!</option>
                                </select>
                            </div>
                            <div v-else class="form-group col-md-2" style="padding-left: 0px;">
                                <label>Cliente</label>
                                <select v-model="clienteElegido" class="form-control pd6" name="id_cliente" value="{{ old('id_cliente') }}">
                                    <option v-for="cliente in clientes" v-bind:value="cliente.id">!{ cliente.nombre }!</option>
                                </select>
                            </div>
    
                            <div v-if="clienteElegido != 2 && fdpagoElegida != 4 && fiado > 0" class="form-group col-md-2" style="padding-left: 0px;">
                                <label>Deberá</label>
                                <input readonly required type="number" min="0" class="form-control pd6 text-center" name="fiado" v-bind:value="fiado" style="background-color: #ffdcdc; color: red; font-weight: bold; font-size: large;">
                            </div>
    
                            <div class="form-group col-md-2" style="padding-left: 0px;">
                                <label>F. de pago</label>
                                <select required v-model="fdpagoElegida" class="form-control pd6" name="id_forma_pago" value="{{ old('id_forma_pago') }}">
                                    <template v-if="clienteElegido == 2">
                                        <option v-for="fdpago in fsdpago" v-bind:value="fdpago.id" v-if="fdpago.id != 4">
                                            !{ fdpago.nombre }!    
                                        </option>
                                    </template>
                                    <template v-else>
                                        <option v-for="fdpago in fsdpago" v-bind:value="fdpago.id">
                                            !{ fdpago.nombre }!    
                                        </option>
                                    </template>
                                </select>
                            </div>
                            <template v-if="(fdpagoElegida == 3 || 
                                                        fdpagoElegida == 8 ||
                                                        fdpagoElegida == 9 ||
                                                        fdpagoElegida == 10 ||
                                                        fdpagoElegida == 11 ||
                                                        fdpagoElegida == 12 ||
                                                        fdpagoElegida == 13 ||
                                                        fdpagoElegida == 14 ||
                                                        fdpagoElegida == 15 ||
                                                        fdpagoElegida == 16 ) && pagoCon > 0">
                                <div class="form-group col-md-2" style="padding-left: 0px;">
                                    <label v-if="(fdpagoElegida == 3 || fdpagoElegida == 8 || fdpagoElegida == 9 || fdpagoElegida == 10)">Efectivo</label>    
                                    <label v-if="(fdpagoElegida == 11 || fdpagoElegida == 12 || fdpagoElegida == 13)">Transferencia</label>    
                                    <label v-if="(fdpagoElegida == 14 || fdpagoElegida == 15)">Tarjeta</label>    
                                    <label v-if="(fdpagoElegida == 16 )">Cheque</label>    
                                    <input required class="form-control pd6 text-center" type="number" min="0" name="pago_efec" v-on:click="resetPagoEfec()" placeholder="Efectivo" v-model.number="pagoEfec">
                                </div>
                                <div class="form-group col-md-2" style="padding-left: 0px;">
                                    <label v-if="(fdpagoElegida == 3 || fdpagoElegida == 11 )">Tarjeta</label>    
                                    <label v-if="(fdpagoElegida == 8 )">Transferencia</label>    
                                    <label v-if="(fdpagoElegida == 9 || fdpagoElegida == 12 || fdpagoElegida == 14)">Cheque</label>    
                                    <label v-if="(fdpagoElegida == 10 || fdpagoElegida == 13 || fdpagoElegida == 15 || fdpagoElegida == 16)">Dolares</label>    

                                    <input required class="form-control pd6 text-center" type="number" min="0" name="pago_tarj" v-on:click="resetPagoTarj()" placeholder="Tarjeta"  v-model="pagoEfecTarj">
                                </div>
                            </template>

                            {{-- Campos para Vale - Solo mostrar si forma de pago incluye Vale (IDs: 19-23) --}}
                            <template v-if="[19, 20, 21, 22, 23].includes(fdpagoElegida)">
                                {{-- Selector de vales para clientes específicos --}}
                                <template v-if="clienteElegido != 2 && valesDisponibles.length > 0">
                                    <div class="form-group col-md-2" style="padding-left: 0px;">
                                        <label>Vale disponible</label>
                                        <select v-model="valeElegido" class="form-control pd6" name="id_vale">
                                            <option value="">Sin vale</option>
                                            <option v-for="vale in valesDisponibles" v-bind:value="vale.id">
                                                !{ vale.codigo_vale }!
                                            </option>
                                        </select>
                                    </div>

                                    {{-- Mostrar monto disponible del vale seleccionado --}}
                                    <div v-if="valeElegido" class="form-group col-md-2" style="padding-left: 0px;">
                                        <label>Disponible</label>
                                        <div class="form-control pd6 text-center" style="background-color: #e8f5e9; color: #2e7d32; font-weight: bold;">
                                            $ !{ montoValeSeleccionado }!
                                        </div>
                                    </div>

                                </template>

                                {{-- Buscador de vale por código para Cliente General --}}
                                <template v-if="clienteElegido == 2">
                                    <div class="form-group col-md-2" style="padding-left: 0px;">
                                        <label>Buscar Vale</label>
                                        <input type="text" v-model="codigoValeBuscado" @input="buscarValePorCodigoDebounce" @keyup.enter="buscarValePorCodigo" class="form-control pd6" placeholder="Ej: 0001 o VALE-20251018">
                                        <input type="hidden" name="id_vale" v-bind:value="valeEncontrado ? valeEncontrado.id : ''">
                                    </div>

                                    {{-- Mostrar monto disponible cuando encuentra el vale --}}
                                    <div v-if="valeEncontrado" class="form-group col-md-2" style="padding-left: 0px;">
                                        <label>Disponible</label>
                                        <div class="form-control pd6 text-center" style="background-color: #e8f5e9; color: #2e7d32; font-weight: bold;">
                                            $ !{ valeEncontrado.monto_disponible }!
                                        </div>
                                    </div>

                                    {{-- Mostrar mensaje si no hay vale encontrado --}}
                                    <div v-if="!valeEncontrado" class="form-group col-md-2" style="padding-left: 0px;">
                                        <label>&nbsp;</label>
                                        <p class="form-control-static text-muted" style="font-size: 11px; margin-top: 7px;">
                                            <i>Ingrese código del vale</i>
                                        </p>
                                    </div>
                                </template>

                                {{-- Campos de monto para formas combinadas con Vale (20-23) --}}
                                <template v-if="[20, 21, 22, 23].includes(fdpagoElegida) && (valeElegido || valeEncontrado)">
                                    <div class="form-group col-md-2" style="padding-left: 0px;">
                                        <label v-if="fdpagoElegida == 20">Efectivo</label>
                                        <label v-if="fdpagoElegida == 21">Tarjeta</label>
                                        <label v-if="fdpagoElegida == 22">Transferencia</label>
                                        <label v-if="fdpagoElegida == 23">Cheque</label>
                                        <input required class="form-control pd6 text-center" type="number" min="0" step="0.01" name="pago_efec" v-on:click="resetPagoEfec()" placeholder="Monto" v-model.number="pagoEfec">
                                    </div>

                                    <div class="form-group col-md-2" style="padding-left: 0px;">
                                        <label>Vale</label>
                                        <input required class="form-control pd6 text-center" type="number" min="0" step="0.01" name="pago_vale" v-on:click="resetPagoVale()" placeholder="Monto Vale" v-model.number="pagoVale" style="background-color: #dcffd7; color: green; font-weight: bold;">
                                    </div>
                                </template>

                                {{-- Solo Vale (ID 19) - monto automático calculado --}}
                                <template v-if="fdpagoElegida == 19 && (valeElegido || valeEncontrado)">
                                    <div class="form-group col-md-2" style="padding-left: 0px;">
                                        <label>Monto Vale</label>
                                        <input readonly class="form-control pd6 text-center" type="number" name="pago_vale" v-bind:value="montoVale" style="background-color: #dcffd7; color: green; font-weight: bold;">
                                    </div>
                                </template>
                            </template>

                            <input name="descuento" type="hidden" v-bind:value="this.descuento">
                            <input name="recargo" type="hidden" v-bind:value="this.recargo">

                        </form>
                    </div>
                    
                    <hr>

                   <!-- Modal -->
                   <div class="modal fade" id="modalMultiProd" tabindex="-1" style="padding-top: 150px;" role="dialog" aria-labelledby="selectPrododalLabel" aria-hidden="true">
                       <div class="modal-dialog" role="document">
                           <div class="modal-content">
                               <div class="modal-header">
                                   <h5 class="modal-title" id="selectProdModalLabel">Seleccionar producto</h5>
                               </div>
                               <div class="modal-body">
                                   <ul class="list-group">
                                     <li v-for="product in products" class="list-group-item"><button class="list-group-item" v-on:click="addSubOrder(product)">              !{ product.nombre }!
                                    <!-- fallback si no hay talle/color -->
                                    - !{ (product.talle && product.talle.nombre) || 'Sin talle' }!
                                    - !{ (product.color && product.color.nombre) || 'Sin color' }!</button></li>
                                   </ul>
                               </div>
                               <div class="modal-footer">
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                               </div>
                           </div>
                       </div>
                   </div>

                   {{-- <!-- Modal -->
                   <div class="modal fade" id="modalServicios" tabindex="-1" style="padding-top: 150px;" role="dialog" aria-labelledby="selectServicioModalLabel" aria-hidden="true">
                       <div class="modal-dialog" role="document">
                        <form autocomplete="off" v-on:submit.prevent="createSubordenSvc" method="post">
                           <div class="modal-content">
                               <div class="modal-header">
                                   <h5 class="modal-title" id="selectServicioModalLabel">Agregar servicio</h5>
                               </div>
                               <div class="modal-body">

                                <div class="form-group col-md-2" style="padding-left: 0px;">
                                    <label>Precio</label>
                                    <input required type="text" class="form-control text-center pd6" id="precio" name="precio" v-model="monto" oninput="validate(this)">
                                </div>

                                <div class="form-group col-md-4" style="padding-left: 0px;padding-right: 0px;">
                                    <label>Descripción</label>
                                    <input required ref="codigo" type="search" class="form-control text-center pd6" id="descripcion" name="descripcion" v-model="descripcion">
                                </div>

                               </div>
                               <div class="modal-footer">
                                   <button type="button" class="btn btn-secondary" id='btnCancelar' data-dismiss="modal">Cancelar</button>
                                   <button type="submit" class="btn btn-danger" >Agregar</button>
                               </div>
                           </div>
                        </form>
                       </div>
                   </div> --}}

                    <div class="form-group col-md-9" style="margin-top: 0px;padding-left: 0px; padding-right: 0px;">
                        <form autocomplete="off" v-on:submit.prevent="createSuborden" method="post">
                        {{-------------------  Productos  -------------------}}
                            <div class="form-group col-md-2" style="padding-left: 0px;">
                                <label>Cantidad</label>
                                <input required type="text" class="form-control text-center pd6" id="cantidad" name="cantidad" v-model="newCant" oninput="validate(this)">
                            </div>
                            
                            <div class="form-group col-md-4" style="padding-left: 0px;padding-right: 0px; position: relative;">
                                <label>Producto</label>
                                <input required ref="codigo" type="search" class="form-control text-center pd6" id="codigo" name="codigo" v-model="newCod" @input="searchProducts" autocomplete="off">
                                <div v-if="productsFound.length > 0" class="autocomplete-results">
                                <a v-for="product in productsFound" @click.prevent="selectProduct(product)" class="autocomplete-result">
                                    !{ product.nombre }!<span v-if="product.categoria"> - !{ product.categoria.nombre }!</span><span v-if="product.color"> - !{ product.color.nombre }!</span>
                                </a>
                            </div>
                            </div>
                                        
                            {{-------------------  Agregar  -------------------}}    
                            <div class="form-group col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-success form-control">
                                    <span class="oi oi-plus"></span>
                                </button>
                            </div>
{{--                             <div class="form-group col-md-4">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-success form-control"  data-toggle="modal" data-target="#modalServicios">
                                    Agregar Servicio
                                </button>
                            </div> --}}
                        </form>
                    </div>

                @endif
            </div>
            
            <table class="table table-striped" style="font-size: 14px; margin-bottom: {{ $order->completada == 1 ? '92px' : '16px' }}; z-index: 1; position: relative;">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Cant.</th>
                        <th scope="col">Producto</th>
                        <th scope="col">P.Unit.</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Hora</th>
                        <th scope="col">Foto</th>
                        @if($order->completada != 1)
                        <th scope="col">Borrar</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="subOrden in subOrdenes">
                        <td style="white-space: nowrap;">
                            @if($order->completada != 1)
                                <input v-if="subOrden.tipo == 'prod'" type="text" class="form-control input-sm text-center"
                                    style="width: 58px; display: inline-block; height: 30px; padding: 2px 4px;"
                                    :value="formatCantidad(subOrden.cantidad)"
                                    @change="updateCantidad(subOrden, $event)"
                                    @keyup.enter="$event.target.blur()">
                                <span v-else>!{ formatCantidad(subOrden.cantidad) }!</span>
                            @else
                                <span>!{ formatCantidad(subOrden.cantidad) }!</span>
                            @endif
                            <b v-if="subOrden.cantidad == 1 && subOrden.unidad && subOrden.unidad.substring(subOrden.unidad.length - 1) === 's'">
                                !{ subOrden.unidad.substring(0, subOrden.unidad.length - 1) }!
                            </b>
                            <b v-else>!{ subOrden.unidad }!</b>
                        </td>
                        <td>
                            !{ subOrden.nombre }!
                            <small class="text-muted">
                                - !{ subOrden.talle }! - !{ subOrden.color }!
                            </small>
                            </td>
                        </td>
                        <td><b>$</b> !{ subOrden.monto }!</td>
                        <td><b>$</b> !{ Math.ceil(subOrden.monto * subOrden.cantidad) }!</td>
                        <td>!{ subOrden.created_at | formatDate }!</td>
                        <td>!{ subOrden.created_at | formatTime }!</td>
                        <td>
                            <img class="zoom" width="36px" v-bind:src="'/storage/products/' + subOrden.archivo">
                        </td>
                        @if($order->completada != 1)
                            <td>
                                <button v-if="subOrden.tipo == 'prod'" class="btn btn-danger" v-on:click.prevent="deleteSuborden(subOrden)" style="height: 34px;">
                                    <span class="oi oi-trash"></span>
                                </button>
                                <button v-else class="btn btn-danger" v-on:click.prevent="deleteSubordenSvc(subOrden)" style="height: 34px;">
                                    <span class="oi oi-trash"></span>
                                </button>
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>

            @if($order->completada != 1)
                <div class="text-center" style="margin-bottom: 30px;">
                    <template v-if="!puedeCerrarOrden">
                        <button type="submit" form="form-cerrar-orden" disabled class="btn btn-danger"><b>Cobrar</b></button>
                    </template>
                    <template v-else>
                        <button type="submit" form="form-cerrar-orden" class="btn btn-danger"><b>Cobrar</b></button>
                    </template>
                </div>
            @endif
            
            @if ($order->completada == 1)
                <div class="row">
                    <div class="col-md-12" style="position: absolute; bottom: 15px;">
                        <div class="row" style="margin-right: 15px;">
                            <div class="col-md-12">
                                <hr style="margin-top: 0px; margin-bottom: 30px;">
                            </div>
                            @if ($order->idAfipFct == 0)
                                <div class="col-md-6">
                                    <a id="btnTicket" href="/admin/verTicket/{{$order->id}}" target="print_popup" class="btn btn-info btn-lg btn-block" type="button" onclick="window.open(this.href,this.target,'width=650,height=650');return false;">TICKET</a>
                                </div>
                                {{-- Solo auto-abrir si el referrer empieza con /admin/control/ingresos/productos --}}
                                    @php
                                        $prevPath = parse_url(URL::previous(), PHP_URL_PATH);
                                    @endphp
                                    @if (\Illuminate\Support\Str::startsWith($prevPath, '/admin/control/ingresos/productos'))
                                        <script>
                                        window.addEventListener('load', function () {
                                            var link = document.getElementById('btnTicket');
                                            if (link) {
                                            window.open(link.href, link.target, 'width=650,height=650');
                                            }
                                        });
                                        </script>
                                    @endif
                          {{--      <div class="col-md-6">
                                    <input class="btn btn-success btn-lg btn-block" type="button" value="FACTURAR" data-toggle="modal" data-target="#createFct">
                                </div>--}}
                            @else
                                <div class="col-md-6">
                                    <a href="/admin/afip/verFactura/{{$order->idAfipFct}}" target="print_popup" class="btn btn-success btn-lg btn-block" type="button" onclick="window.open(this.href,this.target,'width=650,height=650');return false;">VER FACTURA</a>
                                </div>    
                                @if ($order->idAfipNdc == 0)
                                    <div class="col-md-6">
                                        <input class="btn btn-danger btn-lg btn-block" type="button" value="GENERAR NOTA DE CREDITO" data-toggle="modal" data-target="#createNdC">
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <a href="/admin/afip/verNotaDeCredito/{{$order->idAfipNdc}}" target="print_popup" class="btn btn-danger btn-lg btn-block" type="button" onclick="window.open(this.href,this.target,'width=650,height=650');return false;">VER NOTA DE CREDITO</a>
                                    </div>
                                @endif
                            @endif 
                        </div>
                    </div>
                </div>
                
            @endif
        @endif
    </div>
    <script>
        window.App = {
            id_order: {!! json_encode($order->id) !!}
        }
        var validate = function(e) {
            e.value = e.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
            var t = e.value;
            e.value = (t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 4)) : t;
        }
    </script>
@endsection
