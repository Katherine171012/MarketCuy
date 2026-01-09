<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('titulo', 'MarketCuy')</title>

    {{-- Bootstrap (unificado) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- FontAwesome (por si algún módulo lo usa) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body { background-color: #f8f9fa; }

        /* ===== PALETA CONCHO ===== */
        .bg-dark { background-color: #660404 !important; }
        .bg-concho { background-color: #660404 !important; }

        /* Botón concho (por si lo usan en blades) */
        .btn-concho{
            background-color: #660404 !important;
            color: #fff !important;
            border: none !important;
        }
        .btn-concho:hover{
            background-color: #4d0303 !important;
            color: #fff !important;
        }

        /* Tabla header concho (por si usan table-custom) */
        .table-custom thead th{
            background-color: #660404 !important;
            color: #fff !important;
            border: none !important;
            padding: 15px !important;
        }

        /* ===== Tus estilos bootstrap custom ===== */
        .table-dark {
            --bs-table-bg: #660404;
            --bs-table-color: #fff;
            --bs-table-border-color: rgba(255,255,255,.25);
        }

        .btn-outline-dark{
            --bs-btn-color: #660404;
            --bs-btn-border-color: #660404;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #660404;
            --bs-btn-hover-border-color: #660404;
            --bs-btn-active-bg: #660404;
            --bs-btn-active-border-color: #660404;
        }

        .btn-primary{
            --bs-btn-bg: #660404;
            --bs-btn-border-color: #660404;
            --bs-btn-hover-bg: #520303;
            --bs-btn-hover-border-color: #520303;
            --bs-btn-active-bg: #520303;
            --bs-btn-active-border-color: #520303;
        }

        /* SVG en nav (por si algún módulo lo trae) */
        nav svg { max-width: 20px; }

        /* ===== CLIENTES: ocultar navbar interno duplicado (si existe en su blade) ===== */
        body.mod-clientes main nav.navbar{
            display: none !important;
        }

        /* ===== ACCIONES EN TABLA: asegurar colores correctos (clientes/productos) ===== */
        body.mod-clientes main table .btn.btn-warning,
        body.mod-productos main table .btn.btn-warning{
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }
        body.mod-clientes main table .btn.btn-warning:hover,
        body.mod-productos main table .btn.btn-warning:hover{
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
            color: #000 !important;
        }

        body.mod-clientes main table .btn.btn-danger,
        body.mod-productos main table .btn.btn-danger{
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
        }
        body.mod-clientes main table .btn.btn-danger:hover,
        body.mod-productos main table .btn.btn-danger:hover{
            background-color: #bb2d3b !important;
            border-color: #bb2d3b !important;
            color: #fff !important;
        }
    </style>
</head>

@php
    $p = trim(request()->path(), '/');

    $esClientes     = ($p === 'clientes' || str_starts_with($p, 'clientes/'));
    $esProductos    = ($p === 'productos' || str_starts_with($p, 'productos/'));
    $esProveedores  = ($p === 'proveedores' || str_starts_with($p, 'proveedores/'));

    $clasesBody = [];
    if ($esClientes) $clasesBody[] = 'mod-clientes';
    if ($esProductos) $clasesBody[] = 'mod-productos';
    if ($esProveedores) $clasesBody[] = 'mod-proveedores';

    // Ruta "home" segura: si no existe, usamos proveedores o "/"
    $homeUrl = '/';
    try {
        if (function_exists('route') && app('router')->has('home')) {
            $homeUrl = route('home');
        } elseif (app('router')->has('proveedores.index')) {
            $homeUrl = route('proveedores.index');
        } elseif (app('router')->has('clientes.index')) {
            $homeUrl = route('clientes.index');
        }
    } catch (\Throwable $e) {
        $homeUrl = '/';
    }
@endphp

<body class="bg-light {{ implode(' ', $clasesBody) }}">

{{-- ===== NAVBAR GLOBAL (ÚNICO) ===== --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ $homeUrl }}">MarketCuy</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto">
                @if($esClientes)
                    <li class="nav-item"><span class="nav-link active fw-semibold">Clientes</span></li>
                @elseif($esProductos)
                    <li class="nav-item"><span class="nav-link active fw-semibold">Productos</span></li>
                @elseif($esProveedores)
                    <li class="nav-item"><span class="nav-link active fw-semibold">Proveedores</span></li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">

    {{-- Mensajería NUEVA (estándar por código) --}}
    @if(session('codigo_mensaje'))
        @php
            $tipo = session('tipo_mensaje', 'success'); // success|warning|danger
            $texto = config('mensajes.' . session('codigo_mensaje'));
        @endphp

        @if($texto)
            <div class="alert alert-{{ $tipo }} py-2 border-0 shadow-sm small fw-bold mb-3">
                {{ $texto }}
            </div>
        @endif
    @endif

    {{-- Compatibilidad con mensajes VIEJOS (por si algún módulo aún usa ok/warning/error) --}}
    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    {{-- MENSAJE FRONTEND FACTURAS (stock) --}}
    <div id="alerta-stock"
         class="alert alert-warning py-2 border-0 shadow-sm small fw-bold mb-3"
         style="display:none;">
        {{ config('mensajes.M36') }}
    </div>

@if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Tu módulo (proveedores) --}}
    @yield('contenido')

    {{-- Módulos de tus panas (clientes/productos) --}}
    @yield('content')

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
