<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'id_encargado',
        'id_cliente',
        'id_type',
        'id_forma_pago',
        'pago_efec',
        'pago_tarj',
        'pago_cheque',
        'pago_transf',
        'pago_dolares',
        'pago_vale',
        'monto',
        'descuento',
        'recargo',
        'completada',
        'deHoy',
        'id_deuda',
        'idAfipFct',
        'idAfipNdc'
    ];
    public function productos()
    {
        return $this->hasMany(OrderProduct::class, 'id_order');
    }

    /**
     * Relación con vales usados en esta orden
     */
    public function vales()
    {
        return $this->belongsToMany(Vale::class, 'order_vales', 'id_order', 'id_vale')
                    ->withPivot('monto_aplicado')
                    ->withTimestamps();
    }

    /**
     * Relación con cliente
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente', 'id');
    }

    /**
     * Relación con encargado
     */
    public function encargado()
    {
        return $this->belongsTo(User::class, 'id_encargado', 'id');
    }

    /**
     * Devolución de esta orden (si existe)
     */
    public function devolucion()
    {
        return $this->hasOne(Devolucion::class, 'id_order_original', 'id');
    }
}
