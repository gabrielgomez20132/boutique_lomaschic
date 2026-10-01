<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{$name}}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4;
            margin: 2mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2.5mm;
        }

        tr {
            height: auto;
            min-height: 45mm;
        }

        td.etiqueta {
            width: 25%;
            min-height: 45mm;
            border: 1px solid #000;
            padding: 2mm;
            vertical-align: top;
        }

        .talle {
            position: absolute;
            top: 1mm;
            right: 1mm;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #000;
            padding: 0.5mm 1.5mm;
            text-align: center;
            width: 10mm;
        }

        .nombre {
            margin-top: 6mm;
            margin-right: 10mm;
            margin-left: 1mm;
            margin-bottom: 2mm;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            line-height: 1.4;
            word-wrap: break-word;
            min-height: 16mm;
        }

        .codigo-texto {
            text-align: center;
            font-size: 7px;
            margin-top: 1mm;
            margin-bottom: 1mm;
        }

        .codigo-barras {
            text-align: center;
            margin-top: 2mm;
        }

        .codigo-barras img {
            width: 40mm;
            height: 6mm;
        }
    </style>
</head>
<body>
<table>
@foreach ($barcodes as $index => $item)
    @if ($index % 4 === 0)
    <tr>
    @endif
        <td class="etiqueta" style="position: relative;">
            <div class="talle">{{ $item['product']->talle ? $item['product']->talle->nombre : '-' }}</div>
            <div class="nombre">{{ $item['product']->nombre }}</div>
            <div class="codigo-texto">{{ $item['product']->codigo }}</div>
            <div class="codigo-barras">
                <img src="data:image/png;base64,{{ $item['barcode'] }}" alt="Código" />
            </div>
        </td>
    @if ($index % 4 === 3)
    </tr>
    @elseif ($index === count($barcodes) - 1)
        @for ($i = 0; $i < 3 - ($index % 4); $i++)
        <td class="etiqueta" style="border: none;"></td>
        @endfor
    </tr>
    @endif
@endforeach
</table>
</body>
</html>
