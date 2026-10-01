<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LoMasChic') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=caja">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body.login-page {
            min-height: 100%;
            margin: 0;
            background: #f3efe8;
            color: #2b2622;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        .login-wrap {
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border: 1px solid #e7e0d6;
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(43, 38, 34, 0.08);
            padding: 36px 32px 28px;
            text-align: center;
        }

        .login-mark {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: #2b2622;
            color: #f3efe8;
            line-height: 56px;
            font-size: 22px;
            letter-spacing: 0.5px;
        }

        .login-card h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.4px;
        }

        .login-card .login-sub {
            margin: 6px 0 22px;
            color: #8a8178;
            font-size: 14px;
        }

        .login-card .form-control {
            height: 44px;
            border-radius: 8px;
            border-color: #ddd4c8;
            box-shadow: none;
            text-align: left;
        }

        .login-card .form-control:focus {
            border-color: #2b2622;
            box-shadow: 0 0 0 3px rgba(43, 38, 34, 0.08);
        }

        .login-card .form-group {
            margin-bottom: 12px;
        }

        .login-remember {
            text-align: left;
            margin: 4px 0 16px;
            color: #5c554e;
            font-weight: normal;
        }

        .login-card .btn-enter {
            height: 46px;
            border: 0;
            border-radius: 8px;
            background: #2b2622;
            color: #fff;
            font-size: 16px;
            letter-spacing: 0.3px;
        }

        .login-card .btn-enter:hover,
        .login-card .btn-enter:focus {
            background: #161310;
            color: #fff;
        }

        .login-card .login-copy {
            margin: 18px 0 0;
            color: #a39b93;
            font-size: 12px;
        }

        .login-card .alert {
            text-align: left;
            border-radius: 8px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-mark">L</div>
            <h1>{{ config('app.name', 'LoMasChic') }}</h1>
            <p class="login-sub">Ingresá para continuar</p>

            <form method="POST" action="{{ route('login') }}">
                {{ csrf_field() }}

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="form-group">
                    <label for="email" class="sr-only">DNI</label>
                    <input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}" placeholder="DNI" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="sr-only">Contraseña</label>
                    <input id="password" type="password" class="form-control" name="password" placeholder="Contraseña" required>
                </div>

                <label class="login-remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Recordarme
                </label>

                <button class="btn btn-block btn-enter" type="submit">Entrar</button>
            </form>

            <p class="login-copy">&copy; {{ date('Y') }} {{ config('app.name', 'LoMasChic') }}</p>
        </div>
    </div>
</body>
</html>
