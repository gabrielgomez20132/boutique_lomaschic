<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body { background: #f5f5f5; min-height: 100vh; }
        .box {
            max-width: 560px;
            margin: 80px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 35px 30px;
            text-align: center;
        }
        .ok { color: #3c763d; }
        .bad { color: #a94442; }
        .meta { margin-top: 18px; font-size: 13px; color: #777; text-align: left; }
    </style>
</head>
<body>
    <div class="box">
        <h1 class="{{ $estado === 'activo' ? 'ok' : 'bad' }}">{{ $titulo }}</h1>
        <p>{{ $mensaje }}</p>

        @if (!empty($datos))
            <div class="meta">
                <div><strong>Motivo:</strong> {{ $datos['motivo'] ?? '-' }}</div>
                <div><strong>Desde:</strong> {{ $datos['desde'] ?? '-' }}</div>
            </div>
        @endif

        <p class="text-muted" style="margin-top: 25px;">
            Estado actual:
            <strong class="{{ $estado === 'activo' ? 'ok' : 'bad' }}">
                {{ $estado === 'activo' ? 'ACTIVO' : 'BLOQUEADO' }}
            </strong>
        </p>
    </div>
</body>
</html>
