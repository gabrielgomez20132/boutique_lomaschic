<?php

namespace App\Http\Middleware;

use App\Licencia;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckLicencia
{
    /**
     * Si la licencia está bloqueada, nadie usa el sistema.
     * Solo las URLs secretas de licencia quedan habilitadas.
     */
    public function handle($request, Closure $next)
    {
        // URLs secretas + pantalla de bloqueo
        if ($request->is('licencia') || $request->is('licencia/*')) {
            return $next($request);
        }

        if (!Licencia::estaBloqueada()) {
            return $next($request);
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('licencia.bloqueada');
    }
}
