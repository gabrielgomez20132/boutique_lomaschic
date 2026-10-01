<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deuda extends Model
{
    protected $fillable = [
        'id_cliente',
        'id_encargado',
        'monto',
        'tipo',
    ];

    protected $table = 'deudas';
}
