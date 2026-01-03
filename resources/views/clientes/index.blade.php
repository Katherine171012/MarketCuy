@extends('layouts.app')

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark bg-concho mb-4 shadow-sm">
        <div class="container">
            <a href="{{ route('home') }}" class="navbar-brand fw-bold font-monospace text-decoration-none">MarketCuy</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link disabled text-white-50 small">Clientes</span>
            </div>
        </div>
    </nav>

    <div class="container">

        <div class="row align-items-center mb-3">
            <div class="col">
                <h5 class="fw-bold mb-0 text-dark">Módulo Clientes</h5>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('clientes.create') }}" class="btn btn-concho btn-sm fw-bold px-3 py-2 shadow-sm">
                    + Crear nuevo cliente
                </a>
                <a href="{{ route('clientes.buscar.form') }}" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-2 bg-white shadow-sm" style="color: #660404; border-color: #660404;">
                    Consulta por parámetro
                </a>
            </div>
        </div>
        @if(isset($clienteEdit))
            @include('clientes.edit', ['cliente' => $clienteEdit])
        @endif
        @if(isset($clienteDelete))
            @include('clientes.show', ['cliente' => $clienteDelete])
        @endif
        @if(isset($busquedaActiva))
            @include('clientes.buscar')
        @endif
        @if(session('codigo_mensaje'))
            <div class="alert alert-success py-2 border-0 shadow-sm small fw-bold mb-3">
                <i class="fas fa-check-circle me-2"></i>
                {{ config("mensajes." . session('codigo_mensaje')) }}
            </div>
        @endif

        @if(isset($clienteDetalle))
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden" style="border: 1px solid #660404 !important;">
                <div class="card-header py-2 text-white" style="background-color: #660404;">
            <span class="fw-bold small text-uppercase">
                <i class="fas fa-eye me-2"></i>Información del Cliente (Solo Lectura): {{ $clienteDetalle->id_cliente }}
            </span>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase">ID Cliente</label>
                            <input type="text" value="{{ $clienteDetalle->id_cliente }}" class="form-control bg-light border-0" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Identificación</label>
                            <input type="text" value="{{ $clienteDetalle->cli_ruc_ced }}" class="form-control bg-light border-0" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Nombre Completo</label>
                            <input type="text" value="{{ $clienteDetalle->cli_nombre }}" class="form-control bg-light border-0 fw-bold" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Ciudad</label>
                            <input type="text" value="{{ $clienteDetalle->ciudad->ciu_descripcion ?? 'No asignada' }}" class="form-control bg-light border-0" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Correo Electrónico</label>
                            <input type="text" value="{{ $clienteDetalle->cli_mail }}" class="form-control bg-light border-0" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Celular</label>
                            <input type="text" value="{{ $clienteDetalle->cli_celular }}" class="form-control bg-light border-0" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Dirección</label>
                            <input type="text" value="{{ $clienteDetalle->cli_direccion ?? 'N/A' }}" class="form-control bg-light border-0" readonly>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary fw-bold px-5 py-2 shadow-sm" style="border-radius: 6px; background-color: #6c757d; border: none;">
                            Cerrar Vista
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-custom">
                <thead >
                    <tr >
                        <th >ID</th>
                        <th>Nombre del Cliente</th>
                        <th>Cédula / RUC</th>
                        <th>Ciudad</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white">
                    @foreach($clientes as $cliente)
                        @php $idLimpio = str_replace(' ', '', $cliente->id_cliente); @endphp
                        <tr style="{{ $cliente->estado_cli != 'ACT' ? 'background-color: #f9f9f9; color: #aaa;' : '' }}">
                            <td class="px-3 fw-bold text-secondary">{{ $cliente->id_cliente }}</td>
                            <td class="fw-bold text-dark">{{ $cliente->cli_nombre }}</td>
                            <td>{{ $cliente->cli_ruc_ced }}</td>
                            <td>{{ $cliente->ciudad->ciu_descripcion ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($cliente->estado_cli == 'ACT')
                                    <span class="badge rounded-pill bg-success px-3">Activo</span>
                                @elseif($cliente->estado_cli == 'SUS')
                                    <span class="badge rounded-pill bg-warning text-dark px-3">Suspendido</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary opacity-50 px-3">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($cliente->estado_cli == 'ACT')
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm fw-bold px-3 py-1" style="background-color: #ffc107; border:none; font-size: 0.75rem;">Editar</a>
                                        <a href="{{ route('clientes.show', $cliente) }}"
                                           class="btn btn-danger btn-sm fw-bold px-3 py-1 shadow-sm"
                                           style="border: none; font-size: 0.75rem;">
                                            Eliminar
                                        </a>
                                    </div>

                                @else
                                    <a href="{{ route('clientes.detalle', $cliente) }}" class="text-muted small fw-bold text-decoration-none">
                                        <i class="fas fa-eye me-1"></i> Visualizar
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <br>
        @if(isset($clienteDelete))
            @include('clientes.show', ['cliente' => $clienteDelete])
        @endif
        <form method="GET" action="{{ route('clientes.index') }}" class="d-flex align-items-center gap-2">
            <label class="small text-muted">Mostrar</label>

            <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>

            <span class="small text-muted">registros</span>

            <div class="col-md-9 text-center">
                @if($clientes->total() > 0)
                    <span class="small text-muted fw-bold">
                    Mostrando {{ $clientes->lastItem() }} de {{ $clientes->total() }} registros
                </span>
                @endif
            </div>
        </form>


        <div class="d-flex justify-content-center mt-4">
            {{ $clientes->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
