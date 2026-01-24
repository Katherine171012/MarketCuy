<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('titulo', 'MarketCuy')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    @stack('styles')
</head>

@php
    $p = trim(request()->path(), '/');

    $esClientes     = ($p === 'clientes' || str_starts_with($p, 'clientes/'));
    $esProductos    = ($p === 'productos' || str_starts_with($p, 'productos/'));
    $esProveedores  = ($p === 'proveedores' || str_starts_with($p, 'proveedores/'));
    $esFacturas     = ($p === 'facturas' || str_starts_with($p, 'facturas/'));
    $esCompras      = ($p === 'compras' || str_starts_with($p, 'compras/'));

    $clasesBody = [];
    if ($esClientes) $clasesBody[] = 'mod-clientes';
    if ($esProductos) $clasesBody[] = 'mod-productos';
    if ($esProveedores) $clasesBody[] = 'mod-proveedores';
    if ($esFacturas) $clasesBody[] = 'mod-facturas';
    if ($esCompras) $clasesBody[] = 'mod-compras';

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
                @elseif($esFacturas)
                    <li class="nav-item"><span class="nav-link active fw-semibold">Facturas</span></li>
                @elseif($esCompras)
                    <li class="nav-item"><span class="nav-link active fw-semibold">Compras</span></li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">

    @if(session('codigo_mensaje'))
        @php
            $tipo = session('tipo_mensaje', 'success');
            $texto = config('mensajes.' . session('codigo_mensaje'));
        @endphp

        @if($texto)
            <div class="alert alert-{{ $tipo }} py-2 border-0 shadow-sm small fw-bold mb-3">
                {{ $texto }}
            </div>
        @endif
    @endif

    <div id="alerta-stock"
         class="alert alert-warning py-2 border-0 shadow-sm small fw-bold mb-3"
         style="display:none;">
        {{ config('mensajes.M36') }}
    </div>

    @yield('contenido')
    @yield('content')

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/global.js') }}"></script>

@stack('scripts')

</body>
</html>
