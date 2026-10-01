<?php

namespace App;

class Licencia
{
    /**
     * Archivo que indica si el sistema está bloqueado por falta de pago.
     */
    public static function path()
    {
        return storage_path('app/licencia_bloqueada.json');
    }

    public static function estaBloqueada()
    {
        return file_exists(self::path());
    }

    public static function datos()
    {
        if (!self::estaBloqueada()) {
            return null;
        }

        $contenido = @file_get_contents(self::path());
        $data = json_decode($contenido, true);

        return is_array($data) ? $data : [
            'bloqueado' => true,
            'desde' => null,
            'motivo' => 'Pago mensual pendiente',
        ];
    }

    public static function bloquear($motivo = 'Pago mensual pendiente')
    {
        $data = [
            'bloqueado' => true,
            'desde' => date('Y-m-d H:i:s'),
            'motivo' => $motivo,
        ];

        file_put_contents(self::path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $data;
    }

    public static function activar()
    {
        if (file_exists(self::path())) {
            @unlink(self::path());
        }

        return true;
    }

    public static function tokenValido($token)
    {
        $esperado = env('LICENCIA_SECRET');

        if (empty($esperado) || empty($token)) {
            return false;
        }

        return hash_equals((string) $esperado, (string) $token);
    }
}
