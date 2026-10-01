<?php
    if ($regAfipFct->tipoCbteNum == 1) 
    {
        $ndcElegida = "A";
    } 
    else if ($regAfipFct->tipoCbteNum == 6) {
        $ndcElegida = "B";
    }
    else if ($regAfipFct->tipoCbteNum == 11) {
        $ndcElegida = "C";
    }
    else {
        $ndcElegida = "noMostrar";
    }

    if ($regAfipFct->tipoPago == "EFTV" ) {
        $tipoPago = "Efectivo";
    } else {
        $tipoPago = "Tarjeta";
    }
?>
<form action="/admin/afip/generarNotaDeCredito" method="post" target="print_popup" class="needs-validation" novalidate>
    {!!csrf_field()!!}
    <div v-if="ndcElegida !== 'noMostrar'" id="createNdC" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Crear" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="margin-top: 100px;width: 54rem;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="col-md-8 modal-title" id="Crear">Generar Nota de Crédito: <b>@{{ ndcElegida }}</b></h4>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="margin-bottom: 75px;">
                    <div class="form-row">
                        
                        <input type="hidden" value="{{$tipoPago}}" name="tipoPago" class="form-control" required hidden>
                        
                        <div class="col-md-3">
                            <label for="custom-control">Destinatario</label>
                        </div>
                        
                        <div class="col-md-4">
                            <input type="radio" v-if="ndcElegida === 'C'" :checked="docTipo == 99" value="C" name="tipoCbte" class="custom-control-input" v-on:click.prevent required>
                            <input type="radio" v-else value="B" name="tipoCbte" class="custom-control-input" v-on:click.prevent v-model="ndcElegida" required>
                            <label class="custom-control-label">Consumidor Final</label>
                        </div>
                        <div class="col-md-5">
                            <input type="radio" v-if="ndcElegida === 'C'" :checked="docTipo == 80" value="C" name="tipoCbte" class="custom-control-input" v-on:click.prevent required>
                            <input type="radio" v-else value="A" name="tipoCbte" class="custom-control-input" v-on:click.prevent v-model="ndcElegida" required>
                            <label class="custom-control-label" >Responsable Inscripto</label>
                        </div>

                    </div>
                    <br>
                    <br>
                    <div class="form-row" v-if="docTipo == 80" style="margin-bottom: 75px;">
                        <div class="col-md-8 mb-2">
                            <label>Nombre completo o Razón Social</label>
                            <input type="name" name="nombreRS" value="{{$regAfipFct->nombreRS}}" class="form-control" required readonly>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>CUIT (SIN GUIONES)</label>
                            <input value="{{$regAfipFct->docNro}}" name="_cuit" class="form-control text-center" required readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-8">
                            <label>Razón por la cual se emite la N. de Crédito</label>
                            <input type="text" maxlength="100" name="concepto" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Nº Factura asociada</label>
                            <input type="text" name="facturaAsoc" class="form-control" value="{{$regAfipFct->nroCbte}}" required readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="margin-bottom: 15px;">
                    <div class="form-row">
                        <div class="col-md-3">
                            <label>Importe Neto</label>
                            <input name="impNeto" v-if="ndcElegida === 'C'" class="form-control text-center" value="{{$regAfipFct->impTotal}}" required readonly>
                            <input name="impNeto" v-else class="form-control text-center" value="{{$regAfipFct->impNeto}}" required readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Importe IVA</label>
                            <input name="impIVA" class="form-control text-center" value="{{$regAfipFct->impIVA}}" required readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Importe Total</label>
                            <input name="impTotal" class="form-control text-center" value="{{$regAfipFct->impTotal}}" required readonly>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-block" type="submit" style="background: brown;">Enviar</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="form-control" name="id_order" value="{{ $order->id }}">
                <input type="hidden" class="form-control" name="cbte" value="NDC">
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
                $('#createNdC').modal('hide');
                window.open('about:blank','print_popup','width=650,height=650');
            }
            
            form.classList.add('was-validated');
            
        }, false);
    });
}, false);
})();
</script>
<script src="/js/app.js"></script>
<script>
    new Vue({
        el: '#createNdC',
        data: {
            ndcElegida: '<?= $ndcElegida ?>',
            docTipo: '<?= $regAfipFct->docTipo ?>',
        }
    });
</script>
