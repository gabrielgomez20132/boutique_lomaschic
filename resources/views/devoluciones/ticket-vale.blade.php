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
        @page {
            size: auto;
            margin: 0mm;
        }
        html {
            background-color: #FFFFFF;
            margin: 0px;
        }
    </style>
</head>

<body style="font-family:arial;">
    <div class="container-fluid">
        <div class="row">
            <div class="contenedor" style="font-weight: 500;">
                <div class="text-center">
                    <p style="font-size: 11pt; margin-top: 1rem;">
                        COMPROBANTE DE DEVOLUCIÓN
                    </p>
                </div>
                <div class="text-center">
                    <img class="logo" src="/logo-lomaschic.jpg" alt="Lo Más Chic" style="width: 180px; height: auto; padding: 0;">
                </div>
                <p>
                    <address class="text-center" style="font-size:13px;font-weight:bolder;">
                        Lo Más Chic
                        <br>
                        Rivadavia 1063
                        <br>
                        Presentar comprobante para cambio o reclamo.
                    </address>
                </p>
                <hr>
                <div class="row">
                    <div class="flex41 text-left pad15">
                        <address style="font-size:13pt">
                            <b>Devolución Nº</b>
                            <br>
                            {{ $devolucion->id }}
                        </address>
                    </div>
                    <div class="flex16 pad15">
                        <h1 class="text-center" style="font-size:36pt">
                            <strong>V</strong>
                        </h1>
                    </div>
                    <div class="flex41 text-right pad15">
                        <address style="font-size:13pt;margin-right: 5px;">
                            <strong>Fecha: </strong>{{ $devolucion->created_at->format('d/m/y') }}
                            <p>
                            <strong>Hora: </strong>{{ $devolucion->created_at->format('H:i') }} hs
                        </address>
                    </div>
                </div>
                <hr>
                <p>
                    <address class="text-left" style="font-size:13px;font-weight:bolder;">
                        <strong style="font-size:15px;">Cliente: </strong>{{ $devolucion->cliente->nombre ?? 'Cliente General' }}
                        @if($devolucion->orderOriginal)
                            <br>
                            <strong style="font-size:15px;">Orden Original: </strong>#{{ $devolucion->orderOriginal->id }}
                        @endif
                    </address>
                </p>
                <hr>
                <div style="font-size:12pt">
                    <p class="text-center" style="font-weight:bolder;font-size:13pt">
                        Productos Devueltos
                    </p>
                    <table class="table" style="font-size:12pt">
                        @foreach($devolucion->productos as $producto)
                            <tr>
                                <td style="padding: 3pt; width: 10%;" class="text-center">{{ $producto->cantidad_devuelta }}</td>
                                <td style="padding: 3pt; width: 60%;">{{ $producto->producto->nombre ?? 'N/A' }}</td>
                                <td style="padding: 3pt; width: 30%;" class="text-right">${{ number_format($producto->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <hr>
                <p class="text-right" style="font-size:13pt">
                    <strong>Total Devuelto: ${{ number_format($devolucion->monto_total, 2) }}</strong>
                </p>
                <hr>
                <div style="border: 2px solid #000; padding: 10pt; margin: 10pt 0;">
                    <p class="text-center" style="font-weight:bolder;font-size:15pt; margin: 5pt 0;">
                        VALE GENERADO
                    </p>
                    <p class="text-center" style="font-size:18pt; font-weight:bolder; margin: 5pt 0;">
                        {{ $vale->codigo_vale }}
                    </p>
                    <p class="text-center" style="font-size:22pt; font-weight:bolder; margin: 10pt 0;">
                        ${{ number_format($vale->monto_disponible, 2) }}
                    </p>
                    <p class="text-center" style="font-size:11pt; margin: 5pt 0;">
                        <strong>Válido hasta:</strong> {{ \Carbon\Carbon::parse($vale->fecha_vencimiento)->format('d/m/Y') }}
                    </p>
                </div>
                <hr>
                @if($devolucion->observaciones)
                    <p style="font-size:11pt">
                        <strong>Observaciones:</strong> {{ $devolucion->observaciones }}
                    </p>
                    <hr>
                @endif
                <p class="text-center" style="font-size:10pt; margin-top: 15pt;">
                    Este vale puede ser utilizado como medio de pago
                    <br>
                    en futuras compras dentro de los 15 días.
                    <br>
                    <strong>Conserve este comprobante.</strong>
                </p>
            </div>
        </div>
    </div>
    <div class="no-print" style="text-align:center; margin: 20px 0;">
        <button type="button" onclick="volverAlSistema()" style="font-size:14pt; padding:8px 20px; cursor:pointer;">
            Cerrar / Volver al sistema
        </button>
    </div>
    <style>@media print { .no-print { display: none !important; } }</style>
    <script>
        function volverAlSistema() {
            if (window.opener && !window.opener.closed) {
                window.close();
            } else {
                window.location.href = "{{ route('devoluciones.show', $devolucion->id) }}";
            }
        }
        window.onafterprint = function () {
            // Si se abrio como ventana aparte, se cierra sola al terminar de imprimir
            if (window.opener && !window.opener.closed) {
                window.close();
            }
        };
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
