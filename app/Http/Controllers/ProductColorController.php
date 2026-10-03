<?php

namespace App\Http\Controllers;

use App\ProductColor;
use Illuminate\Http\Request;

class ProductColorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = ProductColor::where('activa', 1)->orderBy('id')->get();
        $type = "categoria";
        $activas = true;
        return view('colors.index', compact('categories','type','activas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $type = "categoria";
        return view('colors.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ProductColor::create([
            'nombre' => $request['nombre']
        ]);
        
        return redirect()->route('colors.create')->with('message', 'La categoría fue creada correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = ProductColor::find($id);
        
        if ($category == null) 
        {
            return view('errors.404');
        }

        return view('colors.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $type = "categoria";
        $category = ProductColor::find($id);
        return view('colors.edit', compact('type','category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductColor $category)
    {
        $data = request()->validate([
            'nombre' => 'required',
        ]);
        
        $category->update($data);
        
        return redirect("/admin/colors/");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(ProductColor $category)
    {
        // No permitir borrar si hay productos dados de alta que lo usan
        $usados = \App\Product::where('id_color', $category->id)->count();
        if ($usados > 0) {
            return redirect('/admin/colors/')->with('error', 'No se puede borrar el color "' . $category->nombre . '": lo usa' . ($usados > 1 ? 'n ' . $usados . ' productos' : ' 1 producto') . '. Cambiales el color primero.');
        }

        $category->update(['activa' => 0]);
        return redirect('/admin/colors/');
    }

    public function resurrect(ProductColor $category)
    {
        $category->update(['activa' => 1]);
        return redirect('/admin/colors/');
    }

    public function papelera()
    {
        $categories = ProductColor::where('activa', 0)->orderBy('id')->get();
        $type = "categoria";
        $activas = false;
        return view('colors.index', compact('categories','type','activas'));
    }
}
