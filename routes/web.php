<?php

use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;

// Ahora la raíz del sitio carga la portada directamente
Route::get('/', function () {
    return view('layouts.portada');
})->name('home');

Route::get('/home', function () {
    return redirect()->route('home');
});




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
