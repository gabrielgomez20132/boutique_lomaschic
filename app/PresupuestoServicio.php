<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PresupuestoServicio extends Model
{
    protected $fillable = [
        'id_presupuesto',
        'description',
        'monto'
    ];
}
