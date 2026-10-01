<form action="/admin/afip/generarFactura" method="post" target="print_popup" class="needs-validation" novalidate>
    {!!csrf_field()!!}
    <div id="createFct" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Crear" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="width: 50rem; margin-top: 100px;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="custom-control custom-switch">
                        <div class="col-md-5">
                            <h4 class="modal-title" id="Crear">
                                <div v-if="monotributo.checked">
                                    Generar Factura: <b>C</b>
                                </div>
                                <div v-else>
                                    Generar Factura: <b>@{{ factElegida }}</b>
                                </div>
                            </h4>
                        </div>
                        
                        <div class="col-md-3 hide">
                            <input type="checkbox" class="custom-control-input" v-on:click.prevent v-model="monotributo.checked">
                            <label class="custom-control-label" for="customSwitch1">Factura C</label>
                        </div>

                        <div class="col-md-4 pull-right">
                            <input type="checkbox" class="custom-control-input" v-model="campoOpcional.checked">
                            <label class="custom-control-label" for="customSwitch1">Campo opcional</label>
                            
                            <button class="close" style="margin-top: -10px; margin-right: -20px;" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-row" style="margin-bottom: 85px;">
                        <div class="col-md-5">
                            <label for="custom-control">Destinatario</label>
                            <br>
                            <div class="custom-control custom-radio custom-control-inline" style="margin-right: 0rem">
                                <input type="radio" id="2" value="B" name="tipoCbte" class="custom-control-input" v-model="factElegida">
                                <label class="custom-control-label" for="2">Consumidor Final</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline" style="margin-right: 0rem">
                                <input type="radio" id="1" value="A" name="tipoCbte" class="custom-control-input" required v-model="factElegida">
                                <label class="custom-control-label" for="1">Respons. Inscripto</label>
                            </div>
                        </div>
                        <div class="col-md-4" style="padding-left: 30px; padding-right: initial;">
                            <label for="custom-control">Tipo de pago</label>
                            <br>
                            <div class="custom-control custom-radio custom-control-inline" style="margin-right: 0rem">
                                <input type="radio" id="Efectivo" value="Efectivo" name="tipoPago" class="custom-control-input" required>
                                <label class="custom-control-label" for="Efectivo">Efectivo</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline" style="margin-right: 0rem">
                                <input type="radio" id="Tarjeta" value="Tarjeta" name="tipoPago" class="custom-control-input">
                                <label class="custom-control-label" for="Tarjeta">Tarjeta</label>
                            </div>
                        </div>
                        <div class="col-md-3" style="padding-right: initial;padding-left: 35px;">
                            <label for="custom-control">Concepto</label>
                            <br>
                            <div class="custom-control custom-radio custom-control-inline" style="margin-right: 0rem">
                                <input type="radio" id="Varios" value="Varios" name="concepto" class="custom-control-input" required>
                                <label class="custom-control-label" for="Varios">Varios</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline" style="margin-right: 0rem">
                                <input checked type="radio" id="Detalle" value="Detalle" name="concepto" class="custom-control-input">
                                <label class="custom-control-label" for="Detalle">Detalle</label>
                            </div>
                        </div>
                    </div> 
                    <div class="form-row" v-if="factElegida === 'A'" style="margin-bottom: 160px;">
                        <div class="col-md-7">
                            <label for="validationCustom01">Nombre completo o Razón Social</label>
                            <input type="name" name="nombreRS" class="form-control" id="validationCustom01" placeholder="" required>
                        </div>
                        <div class="col-md-5" style="padding-left: initial;">
                            <label for="validationCustom02">CUIT ( no poner guiones )</label>
                            <input type="number" min="1" name="_cuit" class="form-control" id="validationCustom02" placeholder="" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 30px;">
                    <div class="form-row" v-if="campoOpcional.checked">
                        <div class="col-md-7" style="padding-left: initial;">
                            <label for="validationCustom03" style="float: left;">Concepto o detalle (opcional)</label>
                            <input type="name" name="detalleOpcional" class="form-control" id="validationCustom03" placeholder="" required>
                        </div>
                        <div class="col-md-3" style="padding-left: initial;">
                            <label for="validationCustom04" style="float: left;">Monto</label>
                            <input type="number" min="1" name="montoOpcional" step="0.5" class="form-control" id="validationCustom04" placeholder="" required>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" name="id_order" value="{{ $order->id }}">
                    <input type="hidden" class="form-control" name="cbte" value="FCT">
                    <input v-if="monotributo.checked" type="hidden" class="form-control" value="C" name="tipoCbte">
                    <button v-if="campoOpcional.checked" class="btn btn-primary col-md-2" style="margin-top: 25px; background: brown;" type="submit">Enviar</button>
                    <button v-else class="btn btn-primary btn-block" style="height: auto; margin-top: auto; background: brown;" type="submit">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
        'use strict';
        window.addEventListener('load', function() 
        {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) 
            {
                form.addEventListener('submit', function(event) 
                {
                    if (form.checkValidity() === false) 
                    {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    else
                    {
                        $('#createFct').modal('hide');
                        window.open('about:blank','print_popup','width=650,height=650');
                    }
                    
                    form.classList.add('was-validated');
                    
                }, false);
            });
        }, false);
    })();
</script>
<script>
    new Vue({
        el: '#createFct',
        data: {
            factElegida: 'B',
            campoOpcional: {checked: false},
            monotributo: {checked: true},
        }
    });
</script>
