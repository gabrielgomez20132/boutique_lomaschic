@extends('admin')

@section('content2')
    
    <link href="/css/style.css" rel="stylesheet">
    <div class="col-md-3" style="padding-left: initial;padding-right: initial;">
        <table class="table table-bordered" style="font-size: 16pt;font-weight: bold;text-align: center;">
            <tbody>
                @if (!isset($titulo))
                <tr>
                    <td>
                        <button type="button" style="font-size: initial;"
                        @if($comprobantes == 0)
                        disabled
                        @endif
                        class="btn btn-danger btn-lg btn-block" data-toggle="modal" data-target="#pagarTodoModal">Marcar todo como pagado</button>
                    </td>
                </tr>
                @endif
                <tr>
                    <td>Monto Fact: ${{ sprintf("%.2f", $impTotal) }}</td>
                </tr>
                <tr>
                    <td>Neto Total: ${{ sprintf("%.2f", $netoTotal) }}</td>
                </tr>
                <tr>
                    <td>IVA Total: ${{ sprintf("%.2f", $ivaTotal) }}</td>
                </tr>
                <tr>
                    <td>Comprobantes: {{ $comprobantes }}</td>
                </tr>
            </tbody>
        </table>
        <!-- START Modal -->
        <div class="modal fade bd-example-modal-sm" id="pagarTodoModal" tabindex="-1" role="dialog" aria-labelledby="pagarTodoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="pagarTodoModalLabel">Confirmación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -25px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>
                    <div class="modal-body">
                    <p>¿Está seguro de que desea marcar todo como <b>PAGADO</b>?</p>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="/admin/afip/pagarTodo" type="button" class="btn btn-primary">Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Modal -->
        @if (!isset($titulo))
            <form method="POST" action="{{ url('/admin/afip/filtro') }}">
                {!!csrf_field()!!}
                <div class="form-row">
                    <div class="form-group col-md-6" style="padding-left: initial; padding-right: 5px;">
                        <label style="display: flex;">Desde</label>
                        <input type="date" class="form-control form-control-sm" style="padding: 6px 3px 6px 6px;" id="desde" name="desde" value="{{ date("Y-m-d") }}" required>
                    </div>
                    <div class="form-group col-md-6" style="padding-left: 5px; padding-right: initial;">
                        <label style="display: flex;">Hasta</label>
                        <input type="date" class="form-control form-control-sm" style="padding: 6px 3px 6px 6px;" id="hasta" name="hasta" value="{{ date("Y-m-d", strtotime("+1 day")) }}" required>
                    </div>
                </div>
                <button type="submit" name="search" id="search" style="font-size: initial;" class="btn btn-primary btn-block"><b>FILTRAR</b></button>
            </form>
        @else
            <a href="/admin/afip" style="font-size: initial;font-weight: bold;" class="btn btn-primary btn-block">◄ VOLVER</a>
        @endif
    </div>
    <div class="col-md-9">
        @if (isset($titulo))
            <h2 class="text-center" style="margin: 7px; font-weight: bold;">{{$titulo}}</h2>
        {{-- @else
            <h2 class="text-center" style="margin: 15px; font-weight: bold;">Listado de comprobantes</h2>   --}}
        @endif
        <div class="tableFixHead">
            <table class="table table-bordered table-hover table-condensed" style="font-size: 9pt; margin-bottom: 0px;">
                <thead style="text-align-last: center;">
                    <tr>
                        <th>#</th>
                        <th>FECHA y HORA</th>
                        <th>TIPO</th>
                        <th>Nº COMPBTE</th>
                        <th>NUMERO CAE</th>
                        <th>VENC. CAE</th>
                        {{-- @if ($registro->tipoCbteNum != 11 || $registro->tipoCbteNum != 13) --}}
                            <th>NETO</th>
                            <th>IVA</th>
                        {{-- @endif --}}
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registros as $registro)
                        @if ($registro->tipoCbteNum == 3 || $registro->tipoCbteNum == 8 || $registro->tipoCbteNum == 13)
                        <tr class="alert-success text-center" style="cursor: pointer;" onclick="window.open('/admin/afip/verNotaDeCredito/{{$registro->id}}',this.target,'width=650,height=650');return false;">
                        @else
                        <tr class="alert-danger text-center" style="cursor: pointer;" onclick="window.open('/admin/afip/verFactura/{{$registro->id}}',this.target,'width=650,height=650');return false;">
                        @endif
                            <td>{{ $registro->id }}</td>
                            <td>{{ date("d/m/y ● H:i", strtotime($registro->cbteFch)) }}</td>
                            @php
                                switch ($registro->tipoCbteNum) {
                                    case 1:
                                        $tipoCbte = "FCT [A]";
                                        break;
                                    
                                    case 3:
                                        $tipoCbte = "NdC [A]";
                                        break;

                                    case 6:
                                        $tipoCbte = "FCT [B]";
                                        break;
                                    
                                    case 8:
                                        $tipoCbte = "NdC [B]";
                                        break;

                                    case 11:
                                        $tipoCbte = "FCT [C]";
                                        break;
                                    
                                    case 13:
                                        $tipoCbte = "NdC [C]";
                                        break;

                                    default:
                                        $tipoCbte = "";
                                        break;
                                }    
                            @endphp
                            <td>{{ $tipoCbte }}</td>
                            <td>{{ $registro->nroCbte }}</td>
                            <td>{{ $registro->caeNum }}</td>
                            <td>{{ date("d/m/Y", strtotime($registro->caeFvt)) }}</td>
                            
                            <td style="text-align: end;">
                                @if ($registro->tipoCbteNum == 11 || $registro->tipoCbteNum == 13)
                                    <b>$</b>{{ $registro->impTotal }}
                                @else
                                    <b>$</b>{{ $registro->impNeto }}
                                @endif
                            </td>
                            <td style="text-align: end;"><b>$</b>{{ $registro->impIVA }}</td>
                            <td style="text-align: end;"><b>$</b>{{ $registro->impTotal }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection