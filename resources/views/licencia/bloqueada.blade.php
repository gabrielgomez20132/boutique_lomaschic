<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema suspendido</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            min-height: 100vh;
        }
        .licencia-box {
            max-width: 520px;
            margin: 80px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .licencia-box h1 {
            font-size: 24px;
            margin: 20px 0 10px;
            color: #a94442;
        }
        .licencia-box p {
            color: #555;
            font-size: 15px;
            line-height: 1.5;
        }
        .licencia-icon {
            width: 72px;
            height: 72px;
            line-height: 72px;
            margin: 0 auto;
            border-radius: 50%;
            background: #f2dede;
            color: #a94442;
            font-size: 36px;
        }
        .licencia-meta {
            margin-top: 20px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="licencia-box">
        <div class="licencia-icon">!</div>
        <h1>Sistema suspendido</h1>
        <p>
            El acceso al sistema está temporalmente deshabilitado
        </p>
        <p>
            Comunicate con el administrador para reactivar el servicio.
        </p>
        {{-- @if (!empty($datos['desde']))
            <div class="licencia-meta">
                Suspendido desde: {{ $datos['desde'] }}
            </div>
        @endif --}}
        <p class="text-muted" style="margin-top: 30px;">&copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
