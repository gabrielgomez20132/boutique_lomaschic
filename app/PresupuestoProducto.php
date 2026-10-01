<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PresupuestoProducto extends Model
{
    protected $fillable = [
        'id_presupuesto',
        'id_producto',
        'cantidad',
        'costo_x_uni',
        'monto'
    ];
}
