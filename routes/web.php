<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});


Route::get('/test-log', function () {
    \Log::debug('LOG FUNCIONANDO - DEBUG');
    \Log::info('LOG FUNCIONANDO - INFO');
    \Log::error('LOG FUNCIONANDO - ERROR');
    
    return 'Log generado';
});


Route::get('/storagelink', function () {

    Artisan::call('storage:link');

    return 'Storage link creado correctamente';

})->middleware('auth');

Route::get('/clearcache', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    return "Cache limpiado correctamente";

});

Route::get('/freshdb', function () {

    Artisan::call('migrate:fresh', ['--seed' => true]);

    return "Base de datos reseteada y seeders ejecutados correctamente";

});

Route::get('/migrate', function () {

    Artisan::call('migrate', ['--force' => true]);

    return "Migraciones ejecutadas correctamente";

});

// Control de licencia / pago mensual (solo vos conocés el token del .env)
Route::get('/licencia/bloqueada', 'LicenciaController@bloqueada')->name('licencia.bloqueada');
Route::get('/licencia/{token}/bloquear', 'LicenciaController@bloquear')->name('licencia.bloquear');
Route::get('/licencia/{token}/desbloquear', 'LicenciaController@desbloquear')->name('licencia.desbloquear');
Route::get('/licencia/{token}/estado', 'LicenciaController@estado')->name('licencia.estado');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/admin', 'AdminController@admin')->middleware('is_admin')->name('admin');

Route::group(['middleware' => 'is_admin'], function () 
{
    // <-- Formas de pago -->

    Route::get('/admin/getfdpago', 'FormaPagoController@getFdPago');

    // <-- Reportes -->
    Route::get('/admin/reportes', 'ReporteController@index')->name('reporte.index');
    Route::get('/admin/reporte/semana', 'ReporteController@semana')->name('reporte.semana');
    Route::get('/admin/reporte/mes', 'ReporteController@mes')->name('reporte.mes');
    Route::get('/admin/reporte/personalizado', 'ReporteController@personalizado')->name('reporte.personalizado');
  
    // <-- Afip -->
        
    Route::get('/admin/afip', 'AfipController@index')->name('afip.index');

    Route::post('/admin/afip/generarFactura', 'AfipController@generarCbte');

    Route::post('/admin/afip/generarNotaDeCredito', 'AfipController@generarCbte');

    Route::get('/admin/afip/verFactura/{idAfip}', 'AfipController@verCbte');

    Route::get('/admin/afip/verNotaDeCredito/{idAfip}', 'AfipController@verCbte');

    Route::get('/admin/afip/pagarTodo', 'AfipController@pagarTodo');

    Route::post('/admin/afip/filtro', 'AfipController@filtrarCbtes');

    // <--Ticket -->

    Route::get('/admin/verTicket/{idOrder}', 'AfipController@verTicket');

    // Route::post('/admin/productos/actualizarprecios', 'ProductController@actualizarPrecios');

    // Route::get('/admin/productos/nuevo', 'ProductController@create')->name('products.create');

    // Route::post('/admin/productos', 'ProductController@store');

    // Route::get('/admin/productos/{id}/editar', 'ProductController@edit')->name('products.edit');

    // Route::put('/admin/productos/{product}', 'ProductController@update');

    // Route::delete('/admin/productos/{product}', 'ProductController@delete')->name('products.delete');

    // <-- Productos -->
        
    Route::get('/admin/search/{keywords}', 'SearchProductController@search');

    Route::get('/admin/productos', 'ProductController@index')->name('products.index');

    //Route::get('/admin/productos/buscar', 'ProductController@search');

    Route::post('/admin/productos/filtro', 'ProductController@filter');

    Route::post('/admin/productos/actualizarprecios', 'ProductController@actualizarPrecios');

    Route::get('/admin/productos/nuevo', 'ProductController@create')->name('products.create');

    Route::get('/admin/productos/nuevo-multiple', 'ProductController@createMultiple')->name('products.create.multiple');

    Route::post('/admin/productos', 'ProductController@store');

    Route::post('/admin/productos/multiple', 'ProductController@storeMultiple')->name('products.store.multiple');

    Route::post('/admin/productos/check-codigos', 'ProductController@checkCodigos')->name('products.check-codigos');

    Route::get('/admin/productos/{id}/editar', 'ProductController@edit')->name('products.edit');

    Route::put('/admin/productos/{id}/editar', 'ProductController@update')->name('products.update');

    Route::get('/admin/productos/{product}/generate-bar-code', 'ProductController@generateBarCode')->name('products.generate-bar-code');

    Route::get('/admin/productos/generate-bar-codes', 'ProductController@generateBarCodes')->name('products.generate-bar-codes');

    Route::delete('/admin/productos/{product}', 'ProductController@delete')->name('products.delete');

    // <-- Categorias de productos -->
        
    Route::get('/admin/categorias', 'ProductCategoryController@index')->name('categories.index');

    Route::get('/admin/categorias/nueva', 'ProductCategoryController@create')->name('categories.create');

    Route::get('/admin/categorias/papelera', 'ProductCategoryController@papelera')->name('categories.recycleBin');
    
    Route::delete('/admin/categorias/{category}/resurrect', 'ProductCategoryController@resurrect')->name('categories.resurrect');

    Route::post('/admin/categorias', 'ProductCategoryController@store');

    Route::get('/admin/categorias/{id}/editar', 'ProductCategoryController@edit')->name('categories.edit');

    Route::put('/admin/categorias/{category}', 'ProductCategoryController@update');

    Route::get('/admin/categorias/generate-codes/{id}', 'ProductCategoryController@generateBarCodes')->name('categories.generate-bar-codes');

    Route::delete('/admin/categorias/{category}', 'ProductCategoryController@delete')->name('categories.delete');

    // <-- Colores de productos -->
        
    Route::get('/admin/colors', 'ProductColorController@index')->name('colors.index');

    Route::get('/admin/colors/nueva', 'ProductColorController@create')->name('colors.create');

    Route::get('/admin/colors/papelera', 'ProductColorController@papelera')->name('colors.recycleBin');
    
    Route::delete('/admin/colors/{category}/resurrect', 'ProductColorController@resurrect')->name('colors.resurrect');

    Route::post('/admin/colors', 'ProductColorController@store');

    Route::get('/admin/colors/{id}/editar', 'ProductColorController@edit')->name('colors.edit');

    Route::put('/admin/colors/{category}', 'ProductColorController@update');

    Route::delete('/admin/colors/{category}', 'ProductColorController@delete')->name('colors.delete');

    // <-- Marcas de productos -->

    Route::get('/admin/marcas', 'ProductMarcaController@index')->name('marcas.index');

    Route::get('/admin/marcas/nueva', 'ProductMarcaController@create')->name('marcas.create');

    Route::post('/admin/marcas', 'ProductMarcaController@store');

    Route::get('/admin/marcas/{id}/editar', 'ProductMarcaController@edit')->name('marcas.edit');

    Route::put('/admin/marcas/{marca}', 'ProductMarcaController@update');

    // <-- Talles de productos -->
        
    Route::get('/admin/talles', 'ProductTallesController@index')->name('talles.index');

    Route::get('/admin/talles/nueva', 'ProductTallesController@create')->name('talles.create');

    Route::get('/admin/talles/papelera', 'ProductTallesController@papelera')->name('talles.recycleBin');
    
    Route::delete('/admin/talles/{category}/resurrect', 'ProductTallesController@resurrect')->name('talles.resurrect');

    Route::post('/admin/talles', 'ProductTallesController@store');

    Route::get('/admin/talles/{id}/editar', 'ProductTallesController@edit')->name('talles.edit');

    Route::put('/admin/talles/{category}', 'ProductTallesController@update');

    Route::delete('/admin/talles/{category}', 'ProductTallesController@delete')->name('talles.delete');

    // <-- Control -->
        
    //Route::get('/admin/control/', 'ControlController@inicio')->name('control.caja.inicio');
    Route::get('/admin/control/', 'ControlController@ordenes')->name('control.ingresos.productos');

    Route::get('/admin/control/caja/inicio', 'ControlController@inicio')->name('control.caja.inicio');
  
     // Agregado Fabian
        Route::get('/admin/control/caja/{id}/editar', 'ControlController@editCaja')->name('control.edit');
        Route::put('/admin/control/caja/{caja}', 'ControlController@update');
    // Agregado Fabian

    Route::get('/admin/control/caja/cierre/', 'ControlController@cierre')->name('control.caja.cierre');

    Route::post('/admin/control/', 'ControlController@store');
    
    Route::delete('/admin/control/{id}', 'ControlController@delete')->name('control.delete');

    Route::get('/admin/control/caja/retiros', 'ControlController@retiros')->name('control.caja.retiros');

    Route::post('/admin/control/caja/retiros', 'ControlController@historial_retiros');

    // Control.Deudas
    
    Route::post('/admin/control/deudas/pagar', 'DeudaController@saldar');
    
    // Control.Gastos

    Route::get('/admin/control/gastos/varios', 'ControlController@gastos')->name('control.gastos.varios');

    Route::get('/admin/control/gastos/servicios', 'ControlController@gastos')->name('control.gastos.servicios');

    Route::get('/admin/control/gastos/proveedores', 'ControlController@gastos')->name('control.gastos.proveedores');

    Route::post('/admin/control/gastos/varios', 'ControlController@historial_gastos');

    Route::post('/admin/control/gastos/servicios', 'ControlController@historial_gastos');

    Route::post('/admin/control/gastos/proveedores', 'ControlController@historial_gastos');

    // Control.Ingresos

    Route::get('/admin/control/ingresos/productos', 'ControlController@ordenes')->name('control.ingresos.productos');

    Route::post('/admin/control/ingresos/productos', 'ControlController@store_orden');

    Route::post('/admin/control/ingresos/productos/historial', 'ControlController@historial_ordenes');

    //---DESCUENTO en ORDEN---//
    Route::post('/admin/control/productos/descuento/{id_order}', 'ControlController@descuento_orden');
    
    //-----------FIN----------//
    
    //---CERRAR ORDEN---//
    Route::post('/admin/control/productos/cerrar/{id_order}', 'ControlController@cerrar_orden');
    
    //-----------FIN----------//
    Route::get('/admin/control/ingresos/productos/{id_order}', 'ControlController@subordenes')->name('control.ingresos.productos.agregar');

    Route::post('/admin/control/ingresos/productos/{id_order}', 'ControlController@store_suborden');

    Route::delete('/admin/order/{id}', 'OrderController@delete')->name('order.delete');

    Route::get('/admin/getsubordenes/{id_order}', 'OrderProductController@getSubordenes');

    Route::post('/admin/createsuborden/{id_order}', 'OrderProductController@store');

    Route::post('/admin/createsuborden_prod/{id_order}', 'OrderProductController@store_prod');

    Route::post('/admin/createsuborden_svc/{id_order}', 'OrderProductController@store_service');

    Route::post('/admin/updatesuborden/{id}', 'OrderProductController@updateCantidad');
    Route::delete('/admin/deletesuborden/{id}', 'OrderProductController@delete');
    Route::delete('/admin/deletesuborden_svc/{id}', 'OrderProductController@delete_svc');

    // Control.Movimientos

    Route::get('/admin/control/movimientos/', 'ControlController@movimientos')->name('control.movimientos');

    Route::post('/admin/control/movimientos/historial', 'ControlController@historial_movimientos');

    // <-- Usuarios -->
        
    Route::get('/admin/getclientes', 'UserController@getClientes');

    Route::get('/admin/getvalescliente/{id}', 'UserController@getValesCliente');

    Route::get('/admin/buscarvaleporcod/{codigo}', 'UserController@buscarValePorCodigo');

    // <-- Devoluciones --> (ANTES de las rutas genéricas de usuarios)
    Route::get('/admin/devoluciones', 'DevolucionController@index')->name('devoluciones.index');
    Route::get('/admin/devoluciones/crear', 'DevolucionController@create')->name('devoluciones.create');
    Route::post('/admin/devoluciones', 'DevolucionController@store')->name('devoluciones.store');
    Route::get('/admin/devoluciones/orden/{id}', 'DevolucionController@getOrderDetails');
    Route::get('/admin/devoluciones/{id}/ticket', 'DevolucionController@imprimirTicket')->name('devoluciones.ticket');
    Route::get('/admin/devoluciones/{id}', 'DevolucionController@show')->name('devoluciones.show');

    Route::get('/admin/{type}s', 'UserController@index')->name('users.index');

    Route::get('/admin/{type}s/papelera', 'UserController@papelera');

    Route::delete('/admin/{type}s/{user}/resurrect', 'UserController@resurrect')->name('users.resurrect');

    Route::get('/admin/{type}s/nuevo', 'UserController@create')->name('users.create');

    Route::post('/admin/{type}s', 'UserController@store');

    Route::get('/admin/{type}s/{nombre}', 'UserController@show')->name('users.show'); // ID por USER

    Route::get('/admin/{type}s/{nombre}/editar', 'UserController@edit')->name('users.edit');

    Route::put('/admin/{type}s/{user}', 'UserController@update')->name('users.update');

    Route::delete('/admin/{type}s/{user}', 'UserController@delete')->name('users.delete');

    Route::get('/admin/clientes/{nombre}/historial', 'UserController@record')->name('users.record');

    Route::post('/admin/clientes/{nombre}/historial', 'UserController@historial_record');

});
