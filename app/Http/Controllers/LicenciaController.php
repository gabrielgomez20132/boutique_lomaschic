<?php

namespace App\Http\Controllers;

use App\Licencia;
use Illuminate\Http\Request;

class LicenciaController extends Controller
{
    public function bloqueada()
    {
        if (!Licencia::estaBloqueada()) {
            return redirect('/login');
        }

        return view('licencia.bloqueada', [
            'datos' => Licencia::datos(),
        ]);
    }

    public function bloquear($token)
    {
        if (!Licencia::tokenValido($token)) {
            abort(404);
        }

        $datos = Licencia::bloquear('Pago mensual pendiente');

        return response()->view('licencia.accion', [
            'titulo' => 'Sistema bloqueado',
            'mensaje' => 'El sistema quedó bloqueado. Nadie podrá iniciar sesión hasta que lo desbloquees con tu URL secreta.',
            'estado' => 'bloqueado',
            'datos' => $datos,
        ]);
    }

    public function desbloquear($token)
    {
        if (!Licencia::tokenValido($token)) {
            abort(404);
        }

        Licencia::activar();

        return response()->view('licencia.accion', [
            'titulo' => 'Sistema desbloqueado',
            'mensaje' => 'El pago fue registrado. Los usuarios ya pueden iniciar sesión normalmente.',
            'estado' => 'activo',
            'datos' => null,
        ]);
    }

    public function estado($token)
    {
        if (!Licencia::tokenValido($token)) {
            abort(404);
        }

        $bloqueada = Licencia::estaBloqueada();

        return response()->view('licencia.accion', [
            'titulo' => $bloqueada ? 'Sistema bloqueado' : 'Sistema activo',
            'mensaje' => $bloqueada
                ? 'Actualmente el sistema está suspendido por falta de pago.'
                : 'Actualmente el sistema está habilitado.',
            'estado' => $bloqueada ? 'bloqueado' : 'activo',
            'datos' => Licencia::datos(),
        ]);
    }
}
