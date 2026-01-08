<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\FacturaController;

Route::get('/', function () {
    return view('layouts.portada');
})->name('home');

Route::get('/home', function () {
    return redirect()->route('home');
});

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

Route::prefix('proveedores')->group(function () {
    Route::get('/', [ProveedorController::class, 'index'])
        ->name('proveedores.index');
    Route::get('/crear', [ProveedorController::class, 'create'])
        ->name('proveedores.create');
    Route::post('/', [ProveedorController::class, 'store'])
        ->name('proveedores.store');
    Route::get('/{proveedor}/editar', [ProveedorController::class, 'edit'])
        ->name('proveedores.edit');
    Route::put('/{proveedor}', [ProveedorController::class, 'update'])
        ->name('proveedores.update');
    Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])
        ->name('proveedores.destroy');
});

Route::prefix('facturas')->group(function () {
    Route::get('/', [FacturaController::class, 'index'])
        ->name('facturas.index');
    Route::get('/crear', [FacturaController::class, 'create'])
        ->name('facturas.create');

    Route::post('/', [FacturaController::class, 'store'])
        ->name('facturas.store');
    Route::post('/{idFactura}/aprobar', [FacturaController::class, 'aprobar'])
        ->name('facturas.aprobar');
    Route::get('/{idFactura}/editar', [FacturaController::class, 'edit'])
        ->name('facturas.edit');

    Route::put('/{idFactura}', [FacturaController::class, 'update'])
        ->name('facturas.update');
    Route::delete('/{idFactura}/anular', [FacturaController::class, 'destroy'])
        ->name('facturas.anular');
    Route::get('/buscar', [FacturaController::class, 'buscar'])
        ->name('facturas.buscar');

    Route::post('/buscar', [FacturaController::class, 'ejecutarBusqueda'])
        ->name('facturas.buscar.ejecutar');
});
