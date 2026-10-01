<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="/css/light-bootstrap.css" rel="stylesheet">
		<link href="/css/toastr.min.css" rel="stylesheet">
		<link href="/css/style.css" rel="stylesheet">
		<style type="text/css" media="print">
			@page 
			{
				size:  auto;   /* auto is the initial value */
				margin: 0mm;  /* this affects the margin in the printer settings */
			}

			html
			{
				background-color: #FFFFFF; 
				margin: 0px;  /* this affects the margin on the html before sending to printer */
			}
		</style>
	</head>

	<body style="font-family:arial;">
		<div class="container-fluid">
			<div class="row">
				<div class="contenedor" style="font-weight: 500;">
					<div class="text-center">
						<p style="font-size: 11pt; margin-top: 1rem;">
						</p>
					</div>
					<div style="margin-bottom: 1.5rem;">
						<img class="logo" src="/logo_rdt.png" style="width: 65%; padding: 0pt;">
					</div>
					<p>
						<address class="text-center"  style="font-size:15px;font-weight:bolder;">
							Las Heras 78
							<br>
                                                        Frías, Santiago del Estero.
                                                        <br>
                                                        CUIT: 20-32068810-9
							<br>
                                                        Inicio de actividades: 01-05-2015
						</address>
					</p>
					<hr>
					<div class="row">
						<div class="flex41 text-left pad15">
							<address style="font-size:13pt">
								Presupuesto #{{$presupuesto_id}}
							</address>
						</div>
						<div class="flex16 pad15">
							<h1 class="text-center" style="font-size:36pt">
							<strong>P</strong>
							</h1>
						</div>
						<div class="flex41 text-right pad15">
							<address style="font-size:13pt;margin-right: 5px;">
								<strong>Fecha: </strong>{{ date("d/m/y", strtotime($cbteFch)) }}
								<p>
								<strong>Hora: </strong>{{ date("H:i", strtotime($cbteFch)) }} hs
							</address>
						</div>
					</div>
					@if (isset($caenum))
						<address >
							@if ($docTipo == 80)
								Señor(es): <strong>{{ $nombreRS }}</strong>
								<br>
								CUIT: <strong>{{ $_cuit }}</strong>
								<br>
								IVA: <strong>Responsable Inscripto</strong>
							@elseif($docTipo == 99)
								Señor(es): <strong>Consumidor Final</strong>
								<br>
								IVA: <strong>Consumidor Final</strong>
							@endif

							@if ($cbte == "NDC")
								<br>Factura {{ $tipoCbte }}: <strong> {{ $facturaAsoc }} </strong>
							@endif
						</address>
					@endif
					
					<table class="table table-sm table-bordered table-hover">
						<thead>
							<tr>
								<th>Producto</th>
								<th class="text-center" style="width: 1%;">Importe</th>
							</tr>
						</thead>
						<tbody style="font-size:16pt;">
							@if ($concepto == "Detalle") 
								@foreach ($items as $item)
									<tr>
										<td>
										@if ((int)$item->cantidad == $item->cantidad)
										{{ $item->cantidad }} U x {{ sprintf("%.2f", $item->monto) }}
										@else
										{{ sprintf("%.3f", $item->cantidad) }} U x {{ sprintf("%.2f", $item->monto) }}
										@endif
                                                                                <br/>
                                                                                {{ ucfirst(strtolower($item->nombre)) }}
                                                                                </td>
										<td class="text-center align-bottom"><br/><b>$</b>{{ sprintf("%.2f", ceil($item->monto * $item->cantidad)) }}</td>
									</tr>
								@endforeach
							@else {{--  Si CONCEPTO == VARIOS o si se eligió generar una Nota de Crédito --}}
								@if (isset($caenum) && $detalleOpcional && $montoOpcional) 
									<tr>
										<td>{{ ucfirst(strtolower($concepto)) }}</td>
										<td class="text-center">1</td>
										<td class="text-center"><b>$</b>{{ sprintf("%.2f", ($impTotal - $montoOpcional)) }}</td>
										<td class="text-center"><b>$</b>{{ sprintf("%.2f", ($impTotal - $montoOpcional)) }}</td>
									</tr>
								@else 
									<tr>
										<td>{{ $concepto }}</td>
										<td class="text-center">1</td>
										<td class="text-center"><b>$</b>{{ $impTotal }}</td>
										<td class="text-center"><b>$</b>{{ $impTotal }}</td>
									</tr>
								@endif
							@endif
							
							@if (isset($caenum) && $detalleOpcional && $montoOpcional)
								<tr>
									<td>{{ ucfirst(strtolower($detalleOpcional)) }}</td>
									<td class="text-center">1</td>
									<td class="text-center"><b>$</b>{{ sprintf("%.2f", $montoOpcional) }}</td>
									<td class="text-center"><b>$</b>{{ sprintf("%.2f", $montoOpcional) }}</td>
								</tr>
							@endif
							
							<tr>
								<td colspan="4">&nbsp;</td>
							</tr>
							@if ($descuento > 0 )
							<tr>
								<td colspan="3" class="text-right">
									<strong>Descuento</strong>
								</td>
								<td class="text-right"><b>$</b>{{ $descuento }}</td>
							</tr>
							@endif
							<tr>
								<td class="text-right">
									@if (isset($caenum))
									<strong>Total Factura</strong>
									@else
									<strong>Total</strong>
									@endif
								</td>
								<td class="text-right"><b>$</b>{{ $impTotal }}</td>
							</tr>
						</tbody>
					</table>

                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 margTB">
                                                <img class="logo" src="/logo.png" style="width: 25%; padding: 0pt;">
                                            </div>
                                        </div>
				</div>
			</div>
		</div>
		<script src="/js/app.js"></script>
	</body>
</html>
 @php
	if(isset($codigoBarra))
	{
		echo '<script type="text/javascript">
			$(document)
			.ready(function() 
			{
				toastr.options = {
					timeOut: "1700",
					positionClass: "toast-top-full-width",
					onHidden: function() { 
						window.print();
						window.close();
					}
				}
				toastr.success("Imprimiendo...");
			});
		</script>';
	}
	else
	{
		echo '<script type="text/javascript">
			$(document)
			.ready(function() 
			{
				toastr.options = {
					timeOut: "1700",
					positionClass: "toast-top-full-width",
					onHidden: function() { 
						window.print();
						window.close();
					}
				}
				toastr.success("Imprimiendo...");
			});
			window.onunload = refreshParent;
			function refreshParent() {
				window.opener.location.reload();
			}
		</script>';
	}
@endphp
