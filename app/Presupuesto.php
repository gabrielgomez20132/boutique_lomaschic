<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    protected $fillable = [
        'id_encargado',
        'id_cliente',
        'id_type',
        'monto',
        'descuento',
        'completada'
    ];

    public function cliente()
    {
        return $this->belongsTo('App\User', 'id_cliente');
    }
}
