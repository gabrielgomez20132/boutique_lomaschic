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
            margin: 3mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4mm;
        }

        tr {
            height: auto;
            min-height: 50mm;
        }

        td.etiqueta {
            width: 50%;
            min-height: 50mm;
            border: 1px solid #000;
            padding: 2mm;
            vertical-align: top;
        }

        .zona-imagen {
            width: 30mm;
            text-align: center;
            float: left;
            margin-right: 2mm;
        }

        .zona-imagen img {
            width: 28mm;
            height: 28mm;
        }

        .zona-info {
            margin-left: 30mm;
            position: relative;
            padding-top: 0;
        }

        .talle {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #000;
            padding: 1mm 2mm;
            text-align: center;
            width: 12mm;
        }

        .nombre {
            margin-top: 8mm;
            margin-right: 12mm;
            margin-left: 1mm;
            margin-bottom: 2mm;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            line-height: 1.5;
            word-wrap: break-word;
            min-height: 18mm;
        }

        .codigo-texto {
            text-align: center;
            font-size: 8px;
            margin-bottom: 2mm;
        }

        .codigo-barras {
            text-align: center;
            margin-top: 2mm;
        }

        .codigo-barras img {
            width: 50mm;
            height: 7mm;
        }

        .clear {
            clear: both;
        }
    </style>
</head>
<body>
<table>
@foreach ($barcodes as $index => $item)
    @if ($index % 2 === 0)
    <tr>
    @endif
        <td class="etiqueta">
            <div class="zona-imagen">
                @if (!empty($item['product']->archivo))
                    @php
                        $imagePath = public_path('uploads/' . $item['product']->archivo);
                    @endphp
                    @if (file_exists($imagePath))
                        <img src="{{ $imagePath }}" alt="Producto" />
                    @endif
                @endif
            </div>
            <div class="zona-info">
                <div class="talle">{{ $item['product']->talle ? $item['product']->talle->nombre : '-' }}</div>
                <div class="nombre">{{ $item['product']->nombre }}</div>
                <div class="codigo-texto">{{ $item['product']->codigo }}</div>
                <div class="codigo-barras">
                    <img src="data:image/png;base64,{{ $item['barcode'] }}" alt="Código" />
                </div>
            </div>
            <div class="clear"></div>
        </td>
    @if ($index % 2 === 1)
    </tr>
    @elseif ($index === count($barcodes) - 1)
        <td class="etiqueta" style="border: none;"></td>
    </tr>
    @endif
@endforeach
</table>
</body>
</html>
