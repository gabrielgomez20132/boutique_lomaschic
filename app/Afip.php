<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Afip extends Model
{
    protected $fillable = [
        'cbteFch', 
        'tipoCbteNum', 
        'nroCbte',
        'caeNum',
        'caeFvt',
        'docTipo',
        'docNro',
        'nombreRS',
        'tipoPago',
        'impNeto',
        'impIVA',
        'descuento',
        'impTotal',
        'pagada',
        'nroCbteAsoc',
        'codigoBarra',
        'detalleOpcional',
        'montoOpcional',
        'concepto'
    ];
    
    protected $table = 'afip';
    public $timestamps = false;
}
