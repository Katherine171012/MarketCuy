<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;

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
| 1) Rutas estáticas primero
| 2) Rutas dinámicas al final
*/

// 1. RUTAS ESTÁTICAS (Deben ir primero)
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

// 2. RUTAS DINÁMICAS (Usan {cliente}, deben ir al final)

// Ver detalle (Solo lectura)
Route::get('/clientes/{cliente}/detalle', [ClienteController::class, 'verDetalle'])
    ->name('clientes.detalle');

// Modificar cliente
Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])
    ->name('clientes.edit');

Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])
    ->name('clientes.update');

// Eliminar cliente (Confirmación en cuadrito sobre index)
Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])
    ->name('clientes.show');

Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])
    ->name('clientes.destroy');


/*
|--------------------------------------------------------------------------
| PRODUCTOS (tu módulo)
|--------------------------------------------------------------------------
| Mantengo tus rutas EXACTAS para no romper nada.
*/
Route::prefix('productos')->group(function () {

    // tu entrada actual /productos/menu manda a la PORTADA
    Route::get('/menu', function () {
        return redirect()->route('home');
    })->name('productos.menu');

    Route::get('/consultar', function () {
        return redirect()->route('home');
    })->name('productos.consultar');

    // PANTALLA PRINCIPAL DEL MÓDULO
    Route::get('/', [ProductoController::class, 'index'])
        ->name('productos.index');

    // CRUD
    Route::post('/guardar', [ProductoController::class, 'store'])
        ->name('productos.store');

    // Buscar (GET para paginación)
    Route::get('/buscar', [ProductoController::class, 'buscar'])
        ->name('productos.buscar');

    Route::post('/buscar', [ProductoController::class, 'buscar'])
        ->name('productos.buscar.post');

    Route::put('/{id}', [ProductoController::class, 'update'])
        ->name('productos.update');

    Route::delete('/{id}', [ProductoController::class, 'destroy'])
        ->name('productos.destroy');
});
