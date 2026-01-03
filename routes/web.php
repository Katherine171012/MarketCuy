<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;

/*
|--------------------------------------------------------------------------
| HOME / PORTADA
|--------------------------------------------------------------------------
| Raíz del sitio carga la portada directamente.
*/
Route::get('/', function () {
    return view('layouts.portada');
})->name('home');

Route::get('/home', function () {
    return redirect()->route('home');
});


/*
|--------------------------------------------------------------------------
| CLIENTES (código de tu compañera)
|--------------------------------------------------------------------------
*/
Route::get('/clientes', [ClienteController::class, 'index'])
    ->name('clientes.index');

Route::get('/clientes/crear', [ClienteController::class, 'create'])
    ->name('clientes.create');

Route::get('/clientes/cancelar', [ClienteController::class, 'cancelarEliminacion'])
    ->name('clientes.cancelarEliminacion');

Route::get('/clientes/buscar', [ClienteController::class, 'buscarForm'])
    ->name('clientes.buscar.form');

Route::post('/clientes/buscar', [ClienteController::class, 'buscar'])
    ->name('clientes.buscar');

Route::post('/clientes', [ClienteController::class, 'store'])
    ->name('clientes.store');

// RUTAS DINÁMICAS (al final)
Route::get('/clientes/{cliente}/detalle', [ClienteController::class, 'verDetalle'])
    ->name('clientes.detalle');

Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])
    ->name('clientes.edit');

Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])
    ->name('clientes.update');

Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])
    ->name('clientes.show');

Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])
    ->name('clientes.destroy');


/*
|--------------------------------------------------------------------------
| PRODUCTOS (tu módulo)
|--------------------------------------------------------------------------
*/
Route::prefix('productos')->group(function () {

    Route::get('/menu', function () {
        return redirect()->route('home');
    })->name('productos.menu');

    Route::get('/consultar', function () {
        return redirect()->route('home');
    })->name('productos.consultar');

    Route::get('/', [ProductoController::class, 'index'])
        ->name('productos.index');

    Route::post('/guardar', [ProductoController::class, 'store'])
        ->name('productos.store');

    Route::get('/buscar', [ProductoController::class, 'buscar'])
        ->name('productos.buscar');

    Route::post('/buscar', [ProductoController::class, 'buscar'])
        ->name('productos.buscar.post');

    Route::put('/{id}', [ProductoController::class, 'update'])
        ->name('productos.update');

    Route::delete('/{id}', [ProductoController::class, 'destroy'])
        ->name('productos.destroy');
});


/*
|--------------------------------------------------------------------------
| PROVEEDORES
 */
Route::prefix('proveedores')->group(function () {

    // Pantalla principal
    Route::get('/', [ProveedorController::class, 'index'])
        ->name('proveedores.index');

    // Crear (formulario)
    Route::get('/crear', [ProveedorController::class, 'create'])
        ->name('proveedores.create');

    // Guardar
    Route::post('/', [ProveedorController::class, 'store'])
        ->name('proveedores.store');

    // Editar (redirige al index con ?edit=ID como ya lo tienes)
    Route::get('/{proveedor}/editar', [ProveedorController::class, 'edit'])
        ->name('proveedores.edit');

    // Actualizar
    Route::put('/{proveedor}', [ProveedorController::class, 'update'])
        ->name('proveedores.update');

    // Eliminar lógico (DELETE)
    Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])
        ->name('proveedores.destroy');
});
