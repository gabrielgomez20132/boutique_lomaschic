<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    protected $fillable = [
        'nombre',
        'activa',
    ];
}
