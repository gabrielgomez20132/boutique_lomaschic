<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    protected $table = 'cierres_caja';

    protected $fillable = [
        'apertura',
        'cierre',
        'cerrado_por',
        'cant_ventas',
        'total_efectivo',
        'total_turno',
        'resumen',
    ];

    protected $casts = [
        'resumen' => 'array',
    ];
}
