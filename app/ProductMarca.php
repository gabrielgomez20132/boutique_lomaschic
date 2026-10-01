<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductMarca extends Model
{
    protected $fillable = [
        'nombre',
        'activa',
    ];
}