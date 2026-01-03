@extends('layouts.app')

@section('titulo', 'MarketCuy')

@section('contenido')

    <style>
        /* Ajustes solo para la portada */
        .card-modulo { min-height: 220px; }
        .card-modulo h5 { font-size: 1.25rem; }
        .card-modulo p { font-size: 0.95rem; }
        .btn-modulo { padding: 12px 16px; font-size: 1rem; border-radius: 8px; }
    </style>

    <div class="py-4">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold mb-2" style="color:#660404;">
                MarketCuy
            </h1>
            <p class="text-muted fs-5 mb-0">
                Selecciona un módulo para ingresar al sistema
            </p>
        </div>

        <div class="row g-5 justify-content-center">

            {{-- CLIENTES --}}
            <div class="col-12 col-md-6 col-xl-5">
                <div class="card shadow card-modulo border-0 text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h5 class="fw-semibold mb-1">Clientes</h5>
                            <p class="text-muted mb-0">Gestión de clientes y contactos</p>
                        </div>
                        <hr>
                        <a href="{{ route('clientes.index') }}"
                           class="btn text-white w-100 btn-modulo"
                           style="background-color:#660404;">
                            Entrar a Clientes
                        </a>
                    </div>
                </div>
            </div>

            {{-- PRODUCTOS --}}
            <div class="col-12 col-md-6 col-xl-5">
                <div class="card shadow card-modulo border-0 text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h5 class="fw-semibold mb-1">Productos</h5>
                            <p class="text-muted mb-0">Inventario y gestión de productos</p>
                        </div>
                        <hr>
                        <a href="{{ route('productos.index') }}"
                           class="btn text-white w-100 btn-modulo"
                           style="background-color:#660404;">
                            Entrar a Productos
                        </a>
                    </div>
                </div>
            </div>

            {{-- FACTURAS --}}
            <div class="col-12 col-md-6 col-xl-5">
                <div class="card shadow card-modulo border-0 text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h5 class="fw-semibold mb-1">Facturas</h5>
                            <p class="text-muted mb-0">Ventas, cobros y emisión</p>
                        </div>
                        <hr>
                        @if(\Illuminate\Support\Facades\Route::has('facturas.index'))
                            <a href="{{ route('facturas.index') }}"
                               class="btn text-white w-100 btn-modulo"
                               style="background-color:#660404;">
                                Entrar a Facturas
                            </a>
                        @else
                            <button class="btn btn-outline-dark w-100 btn-modulo" disabled>
                                Módulo aún no conectado
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PROVEEDORES --}}
            <div class="col-12 col-md-6 col-xl-5">
                <div class="card shadow card-modulo border-0 text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h5 class="fw-semibold mb-1">Proveedores</h5>
                            <p class="text-muted mb-0">Registro y control de proveedores</p>
                        </div>
                        <hr>
                        @if(\Illuminate\Support\Facades\Route::has('proveedores.index'))
                            <a href="{{ route('proveedores.index') }}"
                               class="btn text-white w-100 btn-modulo"
                               style="background-color:#660404;">
                                Entrar a Proveedores
                            </a>
                        @else
                            <button class="btn btn-outline-dark w-100 btn-modulo" disabled>
                                Módulo aún no conectado
                            </button>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
