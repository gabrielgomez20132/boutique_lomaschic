<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngresoProducto extends Model
{
    use HasFactory;

    protected $table = 'ingreso_producto';

    protected $fillable = [
        'cantidad',
        'costo',
      	'monto',
        'id_producto',
        'id_user',
    ];

    public function producto() {
        return $this->belongsTo(Product::class, 'id_producto');
    }
    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }
}
