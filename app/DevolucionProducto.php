<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DevolucionProducto extends Model
{
    protected $table = 'devolucion_productos';

    protected $fillable = [
        'id_devolucion',
        'id_producto',
        'cantidad_devuelta',
        'precio_unitario',
        'subtotal',
    ];

    /**
     * Relación con la devolución
     */
    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion', 'id');
    }

    /**
     * Relación con el producto
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_producto', 'id');
    }

    /**
     * Alias para mantener consistencia con el resto del sistema
     */
    public function producto()
    {
        return $this->belongsTo(Product::class, 'id_producto', 'id');
    }
}
