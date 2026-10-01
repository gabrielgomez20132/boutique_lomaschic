<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrdersServices extends Model
{
    protected $fillable = [
        'id_order',
        'description',
        'monto',
    ];
    //
}
