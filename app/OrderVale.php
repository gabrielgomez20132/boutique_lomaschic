<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderVale extends Model
{
    protected $table = 'order_vales';

    protected $fillable = [
        'id_order',
        'id_vale',
        'monto_aplicado',
    ];

    /**
     * Relación con la orden
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id');
    }

    /**
     * Relación con el vale
     */
    public function vale()
    {
        return $this->belongsTo(Vale::class, 'id_vale', 'id');
    }
}
