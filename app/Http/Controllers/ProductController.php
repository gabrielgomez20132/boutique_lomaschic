<?php

namespace App\Http\Controllers;

use App\IngresoProducto;
use App\ProductCategory;
use App\Product;
use App\ProductColor;
use App\ProductTalle;
use App\ProductMarca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $categories = ProductCategory::where('activa', 1)->orderBy('id')->get();
        $talles = ProductTalle::where('activa', 1)->orderBy('nombre')->get();
        $colors = ProductColor::where('activa', 1)->orderBy('nombre')->get();
        $marcas = ProductMarca::where('activa', 1)->orderBy('nombre')->get();

        // Obtener parámetros de ordenamiento desde la URL
        $sortBy = $request->get('sort', 'created_at'); // Por defecto ordenar por fecha de creación
        $sortOrder = $request->get('order', 'desc'); // Por defecto descendente (más recientes primero)

        $products = Product::orderBy($sortBy, $sortOrder)->paginate(20);
        $type = "producto";

        return view('products.index', compact('products', 'categories', 'type', 'colors', 'talles', 'marcas', 'sortBy', 'sortOrder'));
    }

    // public function search(Request $request)
    // {
    //     $keyword = $request->keyword;
    //     $categories = ProductCategory::where('activa', 1)->orderBy('id')->get();
    //     $products = Product::where('nombre', 'LIKE', "%$keyword%")->orderBy('nombre')->paginate(5000);
    //     $type = "producto";

    //     return view('products.index', compact('products','categories','type'));
    // }

    public function filter(Request $request)
    {
        $categories = ProductCategory::where('activa', 1)->orderBy('id')->get();

        $talles = ProductTalle::where('activa', 1)->orderBy('nombre')->get();
        $colors = ProductColor::where('activa', 1)->orderBy('nombre')->get();
        $marcas = ProductMarca::where('activa', 1)->orderBy('nombre')->get();

        $sortBy = $request->get('sort', 'created_at');
$sortOrder = $request->get('order', 'desc');

        $products = Product::when($request->filled('id_categoria'), function ($query) use ($request) {
            $query->where('id_categoria', $request->id_categoria);
        })
        ->when($request->filled('id_talle'), function ($query) use ($request) {
            $query->where('id_talle', $request->id_talle);
        })
        ->when($request->filled('id_color'), function ($query) use ($request) {
            $query->where('id_color', $request->id_color);
        })
        ->when($request->filled('id_marca'), function ($query) use ($request) {
            $query->where('id_marca', $request->id_marca);
        })
        ->orderBy($sortBy, $sortOrder)
        ->paginate(5000);

        $type = "producto";

        return view('products.index', compact('products', 'categories', 'type', 'colors', 'talles', 'marcas', 'sortBy','sortOrder'));
    }

    /**
     * Summary of create
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $type = "producto";
        $categories = ProductCategory::where('activa', 1)->orderBy('id')->get();
        $marcas = ProductMarca::where('activa', 1)->orderBy('nombre')->get();
        $talles = ProductTalle::where('activa', 1)->orderBy('nombre')->get();
        $colors = ProductColor::where('activa', 1)->orderBy('nombre')->get();
        return view('products.create', compact('categories', 'marcas', 'type', 'colors', 'talles' ));
    }

    /**
     * Show the form for creating multiple products with same base data.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function createMultiple()
    {
        $type = "producto";
        $categories = ProductCategory::where('activa', 1)->orderBy('id')->get();
        $marcas = ProductMarca::where('activa', 1)->orderBy('nombre')->get();
        $talles = ProductTalle::where('activa', 1)->orderBy('nombre')->get();
        $colors = ProductColor::where('activa', 1)->orderBy('nombre')->get();
        return view('products.create-multiple', compact('categories', 'marcas', 'type', 'colors', 'talles' ));
    }

    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */


    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|unique:products,codigo',
        ], [
            'codigo.unique' => 'El código de barras que intentas utilizar ya está en uso.',
        ]);

        try {

            DB::beginTransaction();

            // Manejo de imagen
            $filename = "sinImagen.png";

            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $ext = $file->getClientOriginalExtension();
                $filename = $request->codigo . "." . $ext;

                // Guardar imagen en storage/app/public/products
                $file->storeAs('products', $filename, 'public');
            }

            // Crear producto
            $producto = Product::create([
                'nombre' => $request->nombre,
                'id_categoria' => $request->id_categoria,
                'id_marca' => $request->id_marca,
                'id_talle' => $request->id_talle,
                'id_color' => $request->id_color,
                'codigo' => $request->codigo,
                'pedido' => $request->pedido ?? 0,
                'quedan' => $request->pedido ?? 0,
                'costo' => $request->costo,
                'aviso' => $request->aviso,
                'monto' => $request->monto,
                'archivo' => $filename
            ]);

            // Registrar ingreso
            IngresoProducto::create([
                'id_producto' => $producto->id,
                'id_user' => Auth::id(),
                'cantidad' => $request->pedido ?? 0,
                'costo' => $request->costo ?? 0,
                'monto' => $request->monto ?? 0
            ]);

            DB::commit();

            return redirect()
                ->route('products.create')
                ->with('message', 'El producto fue creado correctamente.');

        } catch (\Throwable $th) {

            DB::rollBack();

            // Detectar error de duplicado MySQL
            if (str_contains($th->getMessage(), '1062')) {

                $product = Product::where('codigo', $request->codigo)->first();

                if ($product && $product->nombre == $request->nombre) {
                    return redirect()->route('products.create')
                        ->with('message', 'El código de barras ' . $product->codigo . ' ya fue cargado');
                }

                return redirect()->route('products.create')
                    ->with('error', 'El código de barras ' . $request->codigo . ' ya existe en otro producto');
            }

            // Log real del error
            Log::error('Error al crear producto: ' . $th->getMessage());

            return redirect()
                ->route('products.create')
                ->with('error', 'Ocurrió un error inesperado al crear el producto.');
        }
    }

    /* public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|unique:products,codigo',
        ], [
            'codigo.unique' => 'El código de barras que intentas utilizar ya está en uso.',
        ]);
        try {
            $file = $request->file('archivo');
            if ($file != null) {
                $ext = $file->getClientOriginalExtension(); //obtenemos la extension del archivo
                $filename = $request->codigo . "." . $ext;
                //guardamos
                Storage::disk('local')->put($filename,  \File::get($file));
            } else {
                $filename = "sinImagen.png";
            }

            $producto = Product::create([
                'nombre' => $request['nombre'],
                'id_categoria' => $request['id_categoria'],
                'id_marca' => $request['id_marca'],
                'id_talle' => $request['id_talle'],
                'id_color' => $request['id_color'],
                'codigo' => $request['codigo'],
                // 'ideal' => $request['ideal'],
                'pedido' => $request['pedido'],
                'quedan' => $request['pedido'],
                'costo' => $request['costo'],
                'aviso' => $request['aviso'],
                'monto' => $request['monto'],
                'archivo' => $filename
            ]);
          
          	IngresoProducto::create([
                'id_producto' => $producto->id,
                'id_user' => Auth::user()->id,
                'cantidad' => $request['pedido'],
                'costo' => $request['costo'],
              	'monto' => $request['monto']
            ]);

            return redirect()->route('products.create')->with('message', 'El producto fue creado correctamente.');
        } catch (\Throwable $th) {
            if ($th->getCode() == 1062) {
                $product = Product::where('codigo', $request->codigo)->first();
                if ($product->nombre == $request['nombre']) {
                    return redirect()->route('products.create')->with('message', 'El código de barras ' . $product->codigo  . ' ya fue cargado');
                } else {
                    return redirect()->route('products.create')->with('error', 'El código de barras ' . $product->codigo  . ' ya existe, y pertenece a ' . $product->nombre);
                }
            } else {
                # code... Lo dejamos para futuros posibles errores
                dd('Error ' . $th->getCode() . '. Contacte a Unlimited Soft e informe ESTE número. Puede volver ATRÁS y reintentarlo');
            }
        }
    } */


    /**
     * Store multiple products with same base data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeMultiple(Request $request)
    {
        // Validar datos base
        $baseData = $request->validate([
            'nombre' => 'required|string|max:55',
            'id_categoria' => 'required|integer|exists:product_categories,id',
            'id_marca' => 'required|integer|exists:product_marcas,id',
            'costo' => 'required|numeric|min:0',
            'monto' => 'required|numeric|min:0',
        ]);

        // Validar que al menos haya una variante
        if (!$request->has('variantes') || empty($request->variantes)) {
            return redirect()->back()->with('error', 'Debe agregar al menos una variante del producto.');
        }

        $productosCreados = 0;
        $errores = [];

        foreach ($request->variantes as $index => $variante) {
            try {
                // Validar código único
                if (Product::where('codigo', $variante['codigo'])->exists()) {
                    $errores[] = "Fila " . ($index + 1) . ": El código {$variante['codigo']} ya existe";
                    continue;
                }

                // Procesar imagen si existe
                $filename = "sinImagen.png";
                if ($request->hasFile("variantes.{$index}.archivo")) {
                    $file = $request->file("variantes.{$index}.archivo");
                    $ext = $file->getClientOriginalExtension();
                    $filename = $variante['codigo'] . "." . $ext;
                    Storage::disk('local')->put($filename, \File::get($file));
                }

                // Crear el producto
                $producto = Product::create([
                    'nombre' => $baseData['nombre'],
                    'id_categoria' => $baseData['id_categoria'],
                    'id_marca' => $baseData['id_marca'],
                    'id_talle' => $variante['id_talle'],
                    'id_color' => $variante['id_color'],
                    'codigo' => $variante['codigo'],
                    'pedido' => $variante['ingreso'],
                    'quedan' => $variante['ingreso'],
                    'costo' => $baseData['costo'],
                    'aviso' => $variante['aviso'],
                    'monto' => $baseData['monto'],
                    'archivo' => $filename
                ]);

                // Crear registro de ingreso
                IngresoProducto::create([
                    'id_producto' => $producto->id,
                    'id_user' => Auth::user()->id,
                    'cantidad' => $variante['ingreso'],
                    'costo' => $baseData['costo'],
                    'monto' => $baseData['monto']
                ]);

                $productosCreados++;

            } catch (\Exception $e) {
                $errores[] = "Fila " . ($index + 1) . ": Error al crear producto - " . $e->getMessage();
            }
        }

        $mensaje = "Se crearon {$productosCreados} productos correctamente.";
        if (!empty($errores)) {
            $mensaje .= " Errores: " . implode(', ', $errores);
        }

        return redirect()->route('products.create.multiple')->with('message', $mensaje);
    }

    /**
     * Verifica si una lista de códigos ya existe en la base de datos (AJAX).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCodigos(Request $request)
    {
        $codigos = $request->input('codigos', []);
        
        if (empty($codigos)) {
            return response()->json(['existentes' => []]);
        }

        $existentes = Product::whereIn('codigo', $codigos)
            ->get()
            ->map(function ($product) {
                return $product->nombre . ' - ' . $product->codigo;
            })
            ->toArray();
        
        return response()->json([
            'existentes' => $existentes
        ]);
    }

    /**
     * Summary of show
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $product = Product::find($id);

        if ($product == null) {
            return view('errors.404');
        }

        return view('products.show', compact('product'));
    }

    /**
     * Summary of edit
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $type = "producto";
        $appkey = $this->generateKey();
        $categories = ProductCategory::where('activa', 1)->orderBy('id')->get();
        $talles = ProductTalle::where('activa', 1)->orderBy('nombre')->get();
        $colors = ProductColor::where('activa', 1)->orderBy('nombre')->get();
        $marcas = ProductMarca::where('activa', 1)->orderBy('nombre')->get();

        $product = Product::find($id);
        return view('products.edit', compact('product', 'categories', 'type', 'appkey', 'colors', 'talles', 'marcas'));
    }

    public function generateBarCode(Product $product)
    {
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($product->codigo, $generator::TYPE_CODE_128);
        $barcodeBase64 = base64_encode($barcode);

        $pdf = PDF::loadView('pdf.products.barcode', [
            'barcodes' => [
                [
                    'product' => $product,
                    'barcode' => $barcodeBase64,
                ]
            ],
            'name' => "Codigos de barra - {$product->nombre}"
        ]);

        // Devolver el PDF como respuesta
        return $pdf->download("codigo_de_barra_{$product->nombre}.pdf");
    }

    public function generateBarCodes(Request $request)
    {
        $productIds = $request->input('product_ids');

        if (empty($productIds)) {
            return response()->json(['message' => 'No se seleccionaron productos.'], 400);
        }

        $products = Product::whereIn('id', $productIds)->with('talle')->get();

        $generator = new BarcodeGeneratorPNG();
        $barcodes = [];

        foreach ($products as $product) {
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

        $pdf = PDF::loadView($view, [
            'barcodes' => $barcodes,
            'name' => 'Codigos de barra'
        ]);
        $currentDate = now()->format('Y-m-d');

        return $pdf->download("codigos_de_barra_{$currentDate}.pdf");
    }


    /**
     * Summary of update
     * @param \App\Product $product
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */

    public function update(int $id, Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required',
            'id_categoria' => 'required',
            'id_talle' => 'required',
            'id_color' => 'required',
            'codigo' => 'required|unique:products,codigo,' . $id,
            'pedido' => 'required',
            'aviso' => 'required',
            'costo' => 'required',
            'monto' => 'required',
        ], [
            'codigo.unique' => 'El código de barras que intentas utilizar ya está en uso.',
        ]);

        try {

            DB::beginTransaction();

            $product = Product::findOrFail($id);

            $filename = $product->archivo;

            if ($file = $request->file('archivo')) {

                $ext = $file->getClientOriginalExtension();
                $filename = $request->codigo . "." . $ext;

                Storage::disk('public')->put(
                    'products/' . $filename,
                    \File::get($file)
                );
            }

            $pedido = (int) $request->pedido;

            if ($pedido > 0) {
                $quedan = $product->quedan + $pedido;
            } else {
                $quedan = $product->quedan;
            }

            $product->update([
                'nombre' => $request->nombre,
                'id_categoria' => $request->id_categoria,
                'id_marca' => $request->id_marca,
                'id_talle' => $request->id_talle,
                'id_color' => $request->id_color,
                'codigo' => $request->codigo,
                'pedido' => $pedido,
                'quedan' => $quedan,
                'aviso' => $request->aviso,
                'costo' => $request->costo,
                'monto' => $request->monto,
                'archivo' => $filename
            ]);

            if ($pedido > 0) {
                IngresoProducto::create([
                    'id_producto' => $product->id,
                    'id_user' => Auth::user()->id,
                    'cantidad' => $pedido,
                    'costo' => $request->costo,
                    'monto' => $request->monto
                ]);
            }

            DB::commit();

        } catch (\Throwable $th) {

            DB::rollBack();

            if ($th->getCode() == 1062) {

                $product = Product::where('codigo', $request->codigo)->first();

                if ($product && $product->nombre == $request->nombre) {

                    return redirect()->route('products.edit', $id)
                        ->with('message', 'El código de barras ' . $product->codigo . ' ya fue cargado');

                } else {

                    return redirect()->route('products.edit', $id)
                        ->with('error', 'El código de barras ' . $product->codigo . ' ya existe y pertenece a ' . $product->nombre);

                }

            } else {

                dd('Error ' . $th->getCode() . '. Contacte a Unlimited Soft e informe ESTE número.');

            }
        }

        return redirect("/admin/productos/")
            ->with('message', 'Producto actualizado correctamente');
    }


    /* public function update(int $id, Request $request)
    { 
        $data = $request->validate([
            'nombre' => 'required',
            'id_categoria' => 'required',
            'id_talle' => 'required',
            'id_color' => 'required',
            'codigo' => 'required|unique:products,codigo,' . $id,
            'pedido' => 'required',
            'quedan' => 'required',
            'aviso' => 'required',
            'costo' => 'required',
            'monto' => 'required',
        ], [
            'codigo.unique' => 'El código de barras que intentas utilizar ya está en uso.', // 📌 Mensaje personalizado
        ]);
        try {
            $product = Product::findOrFail($id);
            $filename = $product->archivo;
            if ($file = $request->file('archivo')) {
                $ext = $file->getClientOriginalExtension();
                $filename = $request->codigo . "." . $ext;
                $data['archivo'] = $filename;
                // Store the new file
                Storage::disk('local')->put($filename, \File::get($file));
            }

            if ($data['pedido'] == 0) {
                unset($data['pedido'], $data['quedan']);
            } else {
                $data['quedan'] = $product->quedan + $data['pedido'];
            }
            $product->update($data);
          
          	IngresoProducto::create([
               'id_producto' => $product->id,
               'id_user' => Auth::user()->id,
               'cantidad' => $data['pedido'] ?? 0,
               'costo' => $data['costo'] ?? 0,
               'monto' => $data['monto'] ?? 0
           ]);
        } catch (\Throwable $th) {
            if ($th->getCode() == 1062) {
                $product = Product::where('codigo', $request->codigo)->first();
                if ($product->nombre == $request['nombre']) {
                    return redirect()->route('products.create')->with('message', 'El código de barras ' . $product->codigo  . ' ya fue cargado');
                } else {
                    return redirect()->route('products.create')->with('error', 'El código de barras ' . $product->codigo  . ' ya existe, y pertenece a ' . $product->nombre);
                }
            } else {
                # code... Lo dejamos para futuros posibles errores
                dd('Error ' . $th->getCode() . '. Contacte a Unlimited Soft e informe ESTE número. Puede volver ATRÁS y reintentarlo');
            }
        }

        return redirect("/admin/productos/");
    } */


    /**
     * Summary of delete
     * @param \App\Product $product
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Product $product)
    {
        $product->delete();

        return redirect('/admin/productos/');
    }

    public function actualizarPrecios(Request $request)
    {
        $porcentaje = $request->porcentaje;

        // Construir query base
        $query = Product::query();

        // Aplicar filtro de categoría si no es "todas"
        if ($request->id_categoria != 0) {
            $query->where('id_categoria', $request->id_categoria);
        }

        // Aplicar filtro de marca si no es "todas"
        if ($request->id_marca != 0) {
            $query->where('id_marca', $request->id_marca);
        }

        $products = $query->get();

        foreach ($products as $product) {
            $costo = $product->costo;
            $monto = $product->monto;

            $newCosto = ceil(($costo + ($costo * $porcentaje / 100)));
            $newMonto = ceil($monto + ($monto * $porcentaje / 100));

            $product->costo = $newCosto;
            $product->monto = $newMonto;
            $product->save();
        }

        return redirect('/admin/productos/')->with('message', 'Los precios se han incrementado en un ' . $porcentaje . '%');
    }

    public function generateKey()
    {
        $key = getenv("APP_KEY");

        $key = str_replace('=', '', $key);

        $key = str_replace('base64:/', '', $key);

        $cnt = 0;

        for ($i = 0; $i < strlen($key); $i++) {
            $cnt += ord($key[$i]);
        }

        return $cnt % 10000;
    }
}
