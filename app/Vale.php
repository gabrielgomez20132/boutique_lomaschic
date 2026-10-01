<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Vale extends Model
{
    protected $fillable = [
        'codigo_vale',
        'id_cliente',
        'monto_original',
        'monto_usado',
        'monto_disponible',
        'fecha_emision',
        'fecha_vencimiento',
        'id_devolucion',
        'activo',
    ];

    protected $dates = [
        'fecha_emision',
        'fecha_vencimiento',
    ];

    /**
     * Relación con el cliente
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente', 'id');
    }

    /**
     * Relación con la devolución (si el vale proviene de una devolución)
     */
    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion', 'id');
    }

    /**
     * Relación con las órdenes donde se usó este vale
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_vales', 'id_vale', 'id_order')
                    ->withPivot('monto_aplicado')
                    ->withTimestamps();
    }

    /**
     * Aplicar un monto al vale
     *
     * @param float $monto
     * @return bool
     */
    public function aplicarMonto($monto)
    {
        if ($this->isVencido()) {
            return false;
        }

        if ($monto > $this->monto_disponible) {
            return false;
        }

        $this->monto_usado += $monto;
        $this->monto_disponible -= $monto;

        // Si se usó todo el vale, marcarlo como inactivo
        if ($this->monto_disponible <= 0) {
            $this->activo = false;
        }

        return $this->save();
    }

    /**
     * Obtener el saldo disponible del vale
     *
     * @return float
     */
    public function getSaldoDisponible()
    {
        return $this->monto_disponible;
    }

    /**
     * Verificar si el vale está vencido
     *
     * @return bool
     */
    public function isVencido()
    {
        return Carbon::now()->gt($this->fecha_vencimiento);
    }

    /**
     * Generar un código único para el vale
     * Formato: VALE-YYYYMMDD-XXXX
     *
     * @return string
     */
    public static function generarCodigo()
    {
        $fecha = Carbon::now()->format('Ymd');

        do {
            $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $codigo = "VALE-{$fecha}-{$random}";
        } while (self::where('codigo_vale', $codigo)->exists());

        return $codigo;
    }

    /**
     * Scope para obtener vales activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener vales no vencidos
     */
    public function scopeNoVencidos($query)
    {
        return $query->where('fecha_vencimiento', '>=', Carbon::now());
    }

    /**
     * Scope para obtener vales disponibles (activos, no vencidos y con saldo)
     */
    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)
                    ->where('fecha_vencimiento', '>=', Carbon::now())
                    ->where('monto_disponible', '>', 0);
    }

    /**
     * Scope para obtener vales de un cliente específico
     */
    public function scopeDeCliente($query, $id_cliente)
    {
        return $query->where('id_cliente', $id_cliente);
    }
}
