<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormaPagoController extends Controller
{
    public function getFdPago()
    {
        $fdpago = \DB::table('formas_pago')->select('id', 'nombre')->whereNotIn('id', [7, 10, 13, 15, 16] )->orderBy('id')->get();
        return $fdpago;
    }
}
