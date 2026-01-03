<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('titulo', 'MarketCuy')</title>

    {{-- Bootstrap (unificado) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- FontAwesome (de tu pana) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* ===== BASE ===== */
        body { background-color: #f8f9fa; }

        /* ===== PALETA CONCHO ===== */
        .bg-dark { background-color: #660404 !important; }
        .bg-concho { background-color: #660404 !important; }

        /* Botón concho (por si tu pana lo usa en blades) */
        .btn-concho{
            background-color: #660404 !important;
            color: #fff !important;
            border: none !important;
        }
        .btn-concho:hover{
            background-color: #4d0303 !important;
            color: #fff !important;
        }

        /* Tabla header concho (por si tu pana usa table-custom) */
        .table-custom thead th{
            background-color: #660404 !important;
            color: #fff !important;
            border: none !important;
            padding: 15px !important;
        }

        /* SVG en nav (de tu pana) */
        nav svg { max-width: 20px; }

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
        }

        .btn-primary{
            --bs-btn-bg: #660404;
            --bs-btn-border-color: #660404;
            --bs-btn-hover-bg: #520303;
            --bs-btn-hover-border-color: #520303;
        }

        /* ===== CLIENTES: ocultar navbar INTERNO duplicado ===== */
        body.mod-clientes main nav.navbar{
            display: none !important;
        }

        /* =========================================================
           CLIENTES: SOLO BOTONES SUPERIORES (Crear / Consulta)
           - Crear nuevo cliente = ROJO
           - Consulta por parámetro = BLANCO
           OJO: NO tocamos botones de la tabla (Editar/Eliminar)
        ========================================================== */

        /* 1) Estilo general para el bloque de botones del header */
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn{
            border-radius: .5rem;
            font-weight: 600;
        }

        /* 2) Crear nuevo cliente -> ROJO */
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-outline-dark,
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-secondary,
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-concho{
            background-color: #660404 !important;
            border-color: #660404 !important;
            color: #fff !important;
        }

        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-outline-dark:hover,
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-secondary:hover,
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-concho:hover{
            background-color: #520303 !important;
            border-color: #520303 !important;
            color: #fff !important;
        }

        /* 3) Consulta por parámetro -> BLANCO */
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-light,
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-outline-secondary{
            background-color: #fff !important;
            border-color: #dee2e6 !important;
            color: #212529 !important;
        }

        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-light:hover,
        body.mod-clientes main .d-flex.align-items-center.justify-content-between .btn.btn-outline-secondary:hover{
            background-color: #f8f9fa !important;
            border-color: #ced4da !important;
            color: #212529 !important;
        }

        /* ===== ACCIONES EN TABLA: asegurar colores correctos ===== */
        /* Editar amarillo (warning) */
        body.mod-clientes main table .btn.btn-warning{
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }
        body.mod-clientes main table .btn.btn-warning:hover{
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
            color: #000 !important;
        }

        /* Eliminar rojo (danger) */
        body.mod-clientes main table .btn.btn-danger{
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
        }
        body.mod-clientes main table .btn.btn-danger:hover{
            background-color: #bb2d3b !important;
            border-color: #bb2d3b !important;
            color: #fff !important;
        }
    </style>
</head>

@php
    $p = trim(request()->path(), '/');
    $esClientes  = ($p === 'clientes' || str_starts_with($p, 'clientes/'));
    $esProductos = ($p === 'productos' || str_starts_with($p, 'productos/'));
@endphp

<body class="bg-light {{ $esClientes ? 'mod-clientes' : '' }}">

{{-- ===== NAVBAR GLOBAL (ÚNICO) ===== --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('home') }}">MarketCuy</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto">
                @if($esClientes)
                    <li class="nav-item">
                        <span class="nav-link active fw-semibold">Clientes</span>
                    </li>
                @elseif($esProductos)
                    <li class="nav-item">
                        <span class="nav-link active fw-semibold">Productos</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    {{-- Tu módulo --}}
    @yield('contenido')

    {{-- Módulo de tu pana --}}
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
