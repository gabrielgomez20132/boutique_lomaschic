<?php

namespace App\Http\Controllers;

use App\ProductTalle;
use Illuminate\Http\Request;


class ProductTallesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = ProductTalle::where('activa', 1)->orderBy('nombre')->get();
        $type = "categoria";
        $activas = true;
        return view('talles.index', compact('categories','type','activas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $type = "categoria";
        return view('talles.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ProductTalle::create([
            'nombre' => $request['nombre'],
        ]);
        
        return redirect()->route('talles.create')->with('message', 'La categoría fue creada correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = ProductTalle::find($id);
        
        if ($category == null) 
        {
            return view('errors.404');
        }

        return view('talles.show', compact('category'));
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
        $category = ProductTalle::find($id);
        return view('talles.edit', compact('type','category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductTalle $category)
    {
        $data = request()->validate([
            'nombre' => 'required',
        ]);
        
        $category->update($data);
        
        return redirect("/admin/talles/");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(ProductTalle $category)
    {
        $category->update(['activa' => 0]);
        return redirect('/admin/talles/');
    }

    public function resurrect(ProductTalle $category)
    {
        $category->update(['activa' => 1]);
        return redirect('/admin/talles/');
    }

    public function papelera()
    {
        $categories = ProductTalle::where('activa', 0)->orderBy('nombre')->get();
        $type = "categoria";
        $activas = false;
        return view('talles.index', compact('categories','type','activas'));
    }
}
