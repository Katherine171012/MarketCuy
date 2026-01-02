@extends('layouts.app')

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark bg-concho mb-4 shadow-sm">
        <div class="container"><span class="navbar-brand fw-bold font-monospace">MarketCuy</span></div>
    </nav>

    <div class="container">
        <div class="row align-items-center mb-3">
            <div class="col">
                <h5 class="fw-bold mb-0 text-dark">Resultados Encontrados</h5>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm fw-bold px-3 py-2 bg-white shadow-sm"
                        type="button" data-bs-toggle="collapse" data-bs-target="#panelBusqueda"
                        style="color: #660404; border-color: #660404;">
                    <i class="fas fa-search-plus me-1"></i> Nueva Consulta
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary btn-sm fw-bold px-3 py-2 shadow-sm">Volver</a>
            </div>
        </div>

        {{-- Panel incluido (se abrirá/cerrará con el botón de arriba) --}}
        @include('clientes.buscar')

        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-market">
                    <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nombre del Cliente</th>
                        <th>Cédula / RUC</th>
                        <th>Ciudad</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white">
                    @foreach($clientes as $cliente)
                        <tr style="{{ $cliente->estado_cli != 'ACT' ? 'background-color: #f9f9f9; color: #aaa;' : '' }}">
                            <td class="px-3 fw-bold text-secondary small">{{ $cliente->id_cliente }}</td>
                            <td class="fw-bold text-dark">{{ $cliente->cli_nombre }}</td>
                            <td>{{ $cliente->cli_ruc_ced }}</td>
                            <td>{{ $cliente->ciudad->ciu_descripcion ?? 'N/A' }}</td>
                            <td class="text-center">
                                    <span class="badge rounded-pill {{ $cliente->estado_cli == 'ACT' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                        {{ $cliente->estado_cli == 'ACT' ? 'Activo' : 'Inactivo' }}
                                    </span>
                            </td>
                            <td class="text-center">
                                @if($cliente->estado_cli == 'ACT')
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- BOTÓN EDITAR AMARILLO --}}
                                        <a href="{{ route('clientes.edit', $cliente) }}"
                                           class="btn btn-sm fw-bold px-3 py-1 text-dark"
                                           style="background-color: #ffc107; border: none; font-size: 0.75rem;">Editar</a>

                                        <a href="{{ route('clientes.show', $cliente) }}"
                                           class="btn btn-danger btn-sm fw-bold px-3 py-1 shadow-sm"
                                           style="border:none; font-size: 0.75rem;">Eliminar</a>
                                    </div>
                                @else
                                    <span class="text-muted small">No disponible</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-3 mb-5">
            {{ $clientes->appends(request()->all())->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
