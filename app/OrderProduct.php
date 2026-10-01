<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $fillable = [
        'id_order',
        'id_producto',
        'cantidad',
        'costo_x_uni',
        'monto',
    ];

    protected $table = 'orders_products';
    
   	public function order() {
        return $this->belongsTo(Order::class, 'id_order', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(Product::class, 'id_producto');
    }
}
