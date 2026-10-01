<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id_categoria',
        'id_marca',
        'id_talle',
        'id_color',
        'codigo',
        'nombre',
        'pedido',
        'quedan',
        'aviso',
        'costo',
        'monto',
        'archivo',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class,"id_categoria","id");
    }

    public function marca()
    {
        return $this->belongsTo(ProductMarca::class,"id_marca","id");
    }

    public function talle()
    {
        return $this->belongsTo(ProductTalle::class,"id_talle","id");
    }

    public function color()
    {
        return $this->belongsTo(ProductColor::class,"id_color","id");
    }
  
    public function ingresoProducto()
    {
        return $this->hasMany(IngresoProducto::class, 'id_producto', 'id');
    }
  
     public function getTotalCantidadIngresoProducto()
    {
        return $this->ingresoProducto()->sum('cantidad');
    }
}
