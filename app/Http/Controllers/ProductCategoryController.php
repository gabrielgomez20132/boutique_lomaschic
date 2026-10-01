<?php

namespace App\Http\Controllers;

use App\Product;
use App\ProductCategory;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;
use PDF;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = ProductCategory::where('activa', 1)->orderBy('nombre')->get();
        $type = "categoria";
        $activas = true;
        return view('categories.index', compact('categories','type','activas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $type = "categoria";
        return view('categories.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ProductCategory::create([
            'nombre' => $request['nombre'],
            'unidad' => $request['unidad']
        ]);
        
        return redirect()->route('categories.create')->with('message', 'La categoría fue creada correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = ProductCategory::find($id);
        
        if ($category == null) 
        {
            return view('errors.404');
        }

        return view('categories.show', compact('category'));
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
        $category = ProductCategory::find($id);
        return view('categories.edit', compact('type','category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductCategory $category)
    {
        $data = request()->validate([
            'nombre' => 'required',
            'unidad' => 'required'
        ]);
        
        $category->update($data);
        
        return redirect("/admin/categorias/");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(ProductCategory $category)
    {
        $category->update(['activa' => 0]);
        return redirect('/admin/categorias/');
    }

    public function resurrect(ProductCategory $category)
    {
        $category->update(['activa' => 1]);
        return redirect('/admin/categorias/');
    }

    public function papelera()
    {
        $categories = ProductCategory::where('activa', 0)->orderBy('nombre')->get();
        $type = "categoria";
        $activas = false;
        return view('categories.index', compact('categories','type','activas'));
    }

    public function generateBarCodes(int $id, Request $request)
    {
        $products = Product::where('id_categoria', $id)->with('talle')->get();
        $category = ProductCategory::find($id);
        $barcodes = [];

        foreach ($products as $product) {
            $generator = new BarcodeGeneratorPNG();
            $barcode = $generator->getBarcode($product->codigo, $generator::TYPE_CODE_128);
            $barcodeBase64 = base64_encode($barcode);

            $barcodes[] = [
                'product' => $product,
                'barcode' => $barcodeBase64,
            ];
        }

        // Determinar qué vista usar según el parámetro 'columns'
        $columns = $request->input('columns', 4); // Por defecto 4 columnas
        $view = $columns == 2 ? 'pdf.products.barcode-2col' : 'pdf.products.barcode';

        $pdf = PDF::loadView($view, ['barcodes' => $barcodes, 'name' => "Codigos de barra - {$category->nombre}"]);

        return $pdf->download("codigos_de_barra_{$category->nombre}.pdf");
    }
}
