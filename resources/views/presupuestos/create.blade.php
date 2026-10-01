@extends('control.index')

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
                        @if($presupuesto->completada == 1)
                            
                            @if($presupuesto->descuento > 0)
                                <label class="btn btn-danger">DESC. ${{ $presupuesto->descuento }}</label>
                            @endif
                            
                        @else
                            <button class="btn btn-success">TOTAL $ !{ totalSumaPresupuesto }!</button>
                        @endif
                    </div>
                    @if($presupuesto->completada == 1)
                        <div style="margin-top: 3px; margin-left: 5px;">
                            @if(str_contains(URL::previous(), 'clientes'))
                                <a href="{{ URL::previous() }}" class="btn btn-primary"><b>VOLVER</b></a>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
            <div style="display: flex;">
                <div style="margin-left: auto; margin-top: 3px;width:450px;">
                </div>
                <div style="margin-left: auto; margin-top: 3px;">
                    @if($presupuesto->completada == 1)
                    @else
                        <div v-if="(totalSumaPresupuesto > 0 || descuento > 0)" class="input-group" style="width: 120px;">
                            <div class="input-group-addon" style="font-size: 20px;color: white;background-color: #ff0000;">%</div>
                            <input placeholder="DESC" type="text" v-model="perc_desc" class="form-control" style="color: red; font-weight: bold; padding: 1px; font-size: 20px;text-align: center;" oninput="this.value = this.value.replace(/\D+/g, '')">
                        </div>
                    @endif
                </div>
                <div style="margin-left: auto; margin-top: 3px;">
                    @if($presupuesto->completada == 1)
                        
                    @else
                        <div v-if="(totalSumaPresupuesto > 0 || descuento > 0)" class="input-group" style="width: 120px;">
                            <div class="input-group-addon" style="font-size: 20px;color: white;background-color: #ff0000;">$</div>
                            <input placeholder="DESC" type="text" v-model="dec_desc" class="form-control" style="color: red; font-weight: bold; padding: 1px; font-size: 20px;text-align: center;" oninput="this.value = this.value.replace(/\D+/g, '')">
                        </div>
                    @endif
                </div>
            </div>
        @if($presupuesto->completada == 1)
            <p>
                <h4 class="mt-2 mb-3">{{ $subtitulo }}</h4>
            </p>
        @endif
        @if($subtitulo != "La orden todavía no existe")
            <div style="margin-top: 2px; margin-left: auto;">
                @if($presupuesto->completada != 1)
                    <div class="form-group col-md-12" style="margin-top: 0px;padding-left: 0px; padding-right: 0px;">
                        <form method="POST" action="{{ route('presupuestos.grabar', $presupuesto->id) }}">
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
    
                            <input name="descuento" type="hidden" v-bind:value="this.descuento">

                            <div class="form-group col-md-2" style="padding-left: 0px; padding-right: 0px;">
                                <label>&nbsp;</label>
                                <template v-if="itemsPresupuesto.length <= 0 ">
                                    <button type="submit" disabled class="btn btn-danger btn-block"><b>CERRAR</b></button>
                                </template>
                                <template v-else>
                                    <button type="submit" class="btn btn-danger btn-block"><b>CERRAR</b></button>
                                </template>
                            </div>
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
                                     <li v-for="product in products" class="list-group-item"><button class="list-group-item" v-on:click="addItemPresupuesto(product)">!{ product.nombre }!</button></li>
                                   </ul>
                               </div>
                               <div class="modal-footer">
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                               </div>
                           </div>
                       </div>
                   </div>

                   <!-- Modal -->
                   <div class="modal fade" id="modalServicios" tabindex="-1" style="padding-top: 150px;" role="dialog" aria-labelledby="selectServicioModalLabel" aria-hidden=vtrue">
                       <div class="modal-dialog" role="document">
                        <form autocomplete="off" v-on:submit.prevent="createItemSvc" method="post">
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
                   </div>

                    <div class="form-group col-md-9" style="margin-top: 0px;padding-left: 0px; padding-right: 0px;">
                        <form autocomplete="off" v-on:submit.prevent="createItemPresupuesto" method="post">
                        {{-------------------  Productos  -------------------}}
                            <div class="form-group col-md-2" style="padding-left: 0px;">
                                <label>Cantidad</label>
                                <input required type="text" class="form-control text-center pd6" id="cantidad" name="cantidad" v-model="newCant" oninput="validate(this)">
                            </div>
                            
                            <div class="form-group col-md-4" style="padding-left: 0px;padding-right: 0px;">
                                <label>Producto</label>
                                <input required ref="codigo" type="search" class="form-control text-center pd6" id="codigo" name="codigo" v-model="newCod">
                            </div>
                                        
                            {{-------------------  Agregar  -------------------}}    
                            <div class="form-group col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-success form-control">
                                    <span class="oi oi-plus"></span>
                                </button>
                            </div>
                            <div class="form-group col-md-4">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-success form-control"  data-toggle="modal" data-target="#modalServicios">
                                    Agregar Servicio
                                </button>
                            </div>
                        </form>
                    </div>

                @endif
            </div>
            
            <table class="table table-striped" style="font-size: 14px; margin-bottom: 92px; z-index: 1; position: relative;">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Cant.</th>
                        <th scope="col">Producto</th>
                        <th scope="col">P.Unit.</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Hora</th>
                        <th scope="col">Foto</th>
                        @if($presupuesto->completada != 1)
                        <th scope="col">Borrar</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in itemsPresupuesto">
                        <td v-if="parseInt(item.cantidad) == item.cantidad">!{ item.cantidad }! 
                            <b v-if="item.cantidad == 1 && item.unidad.substring(item.unidad.length - 1) ==='s'">
                                !{ item.unidad.substring(0, item.unidad.length - 1) }!
                            </b>
                            <b v-else>
                                !{ item.unidad }!
                            </b>
                        </td>
                        <td v-else>!{ item.cantidad.toFixed(3) }! 
                            <b>!{ item.unidad }!</b>
                        </td>
                        <td>!{ item.nombre }!</td>
                        <td><b>$</b> !{ item.monto }!</td>
                        <td><b>$</b> !{ Math.ceil(item.monto * item.cantidad) }!</td>
                        <td>!{ item.created_at | formatDate }!</td>
                        <td>!{ item.created_at | formatTime }!</td>
                        <td>
                            <img class="zoom" width="36px" v-bind:src="'/uploads/' + item.archivo">
                        </td>
                        @if($presupuesto->completada != 1)
                            <td>
                                <button v-if="item.tipo == 'prod'" class="btn btn-danger" v-on:click.prevent="deleteItemPres(item)" style="height: 34px;">
                                    <span class="oi oi-trash"></span>
                                </button>
                                <button v-else class="btn btn-danger" v-on:click.prevent="deleteItemPresSvc(item)" style="height: 34px;">
                                    <span class="oi oi-trash"></span>
                                </button>
                            </td>
                        @endif

                    </tr>
                </tbody>
            </table>
            
            @if ($presupuesto->completada == 1)
                <div class="row">
                    <div class="col-md-12" style="position: absolute; bottom: 15px;">
                        <div class="row" style="margin-right: 15px;">
                            <div class="col-md-12">
                                <hr style="margin-top: 0px; margin-bottom: 30px;">
                            </div>
                            <div class="col-md-6">
                                <a href="/admin/presupuestos/verTicket/{{$presupuesto->id}}" target="print_popup" class="btn btn-info btn-lg btn-block" type="button" onclick="window.open(this.href,this.target,'width=650,height=650');return false;">TICKET</a>
                            </div>

                        </div>
                    </div>
                </div>
                
            @endif
        @endif
    </div>
    <script>
        window.App = {
            id_presupuesto: {!! json_encode($presupuesto->id) !!}
        }
        var validate = function(e) {
            e.value = e.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
            var t = e.value;
            e.value = (t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 4)) : t;
        }
    </script>
@endsection
