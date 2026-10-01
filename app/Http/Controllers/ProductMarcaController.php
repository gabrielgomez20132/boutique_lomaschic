<?php

namespace App\Http\Controllers;

use App\ProductMarca;
use Illuminate\Http\Request;

class ProductMarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $marcas = ProductMarca::where('activa', 1)->orderBy('nombre')->get();
        $type = "marca";
        return view('marcas.index', compact('marcas','type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $type = "marca";
        return view('marcas.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:product_marcas,nombre',
        ]);

        ProductMarca::create([
            'nombre' => $request['nombre']
        ]);

        return redirect()->route('marcas.create')->with('message', 'La marca fue creada correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $marca = ProductMarca::find($id);
        $type = "marca";
        return view('marcas.edit', compact('type','marca'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductMarca $marca)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:product_marcas,nombre,' . $marca->id,
        ]);

        $marca->update([
            'nombre' => $request['nombre']
        ]);

        return redirect()->route('marcas.index')->with('message', 'La marca fue actualizada correctamente.');
    }

}