<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    protected $table = 'devoluciones';

    protected $fillable = [
        'id_order_original',
        'id_cliente',
        'id_encargado',
        'monto_total',
        'observaciones',
    ];

    /**
     * Relación con la orden original
     */
    public function orderOriginal()
    {
        return $this->belongsTo(Order::class, 'id_order_original', 'id');
    }

    /**
     * Relación con el cliente
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente', 'id');
    }

    /**
     * Relación con el encargado
     */
    public function encargado()
    {
        return $this->belongsTo(User::class, 'id_encargado', 'id');
    }

    /**
     * Relación con los productos devueltos
     */
    public function productos()
    {
        return $this->hasMany(DevolucionProducto::class, 'id_devolucion', 'id');
    }

    /**
     * Relación con el vale generado
     */
    public function vale()
    {
        return $this->hasOne(Vale::class, 'id_devolucion', 'id');
    }

    /**
     * Calcular el monto total de la devolución
     *
     * @return float
     */
    public function calcularMontoTotal()
    {
        return $this->productos()->sum('subtotal');
    }
}
