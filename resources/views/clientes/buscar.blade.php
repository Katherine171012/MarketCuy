@extends('layouts.app')

@section('content')
    {{-- BARRA DE NAVEGACIÓN --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-concho mb-4 shadow-sm">
        <div class="container"><span class="navbar-brand fw-bold font-monospace">MarketCuy</span></div>
    </nav>

    <div class="container">
        {{-- ENCABEZADO DE SECCIÓN Y BOTONES DE ACCIÓN --}}
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

        {{-- PANEL DE BÚSQUEDA (UNIFICADO) --}}
        <div class="collapse mb-4 {{ (isset($busquedaActiva) || request('valor_texto') || request('valor_ciudad')) ? 'show' : '' }}" id="panelBusqueda">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="border: 1px solid #660404 !important;">
                <div class="card-header py-2 text-white" style="background-color: #660404;">
                    <span class="fw-bold small text-uppercase"><i class="fas fa-search me-2"></i>Buscar por Parámetro</span>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST" action="{{ route('clientes.buscar') }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Criterio:</label>
                                <select name="campo" id="criterioBusqueda" class="form-select form-select-sm border-0 bg-light shadow-sm">
                                    <option value="cli_nombre" data-ph="Ingrese el nombre..." {{ request('campo') == 'cli_nombre' ? 'selected' : '' }}>Nombre</option>
                                    <option value="cli_ruc_ced" data-ph="Ingrese Cédula o RUC..." {{ request('campo') == 'cli_ruc_ced' ? 'selected' : '' }}>Cédula / RUC</option>
                                    <option value="cli_mail" data-ph="Ingrese el correo..." {{ request('campo') == 'cli_mail' ? 'selected' : '' }}>Correo</option>
                                    <option value="id_ciudad" data-ph="Seleccione la ciudad..." {{ request('campo') == 'id_ciudad' ? 'selected' : '' }}>Ciudad</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label id="labelDinamico" class="form-label small fw-bold text-muted text-uppercase">
                                    {{ request('campo') == 'id_ciudad' ? 'Seleccione la ciudad...' : (request('campo') == 'cli_ruc_ced' ? 'Ingrese Cédula o RUC...' : (request('campo') == 'cli_mail' ? 'Ingrese el correo...' : 'Ingrese el nombre del cliente')) }}
                                </label>

                                <input type="text" name="valor_texto" id="inputTexto"
                                       class="form-control form-control-sm border-0 bg-light shadow-sm {{ request('campo') == 'id_ciudad' ? 'd-none' : '' }}"
                                       value="{{ request('valor_texto') }}" placeholder="...">

                                <select name="valor_ciudad" id="selectCiudad"
                                        class="form-select form-select-sm border-0 bg-light shadow-sm {{ request('campo') == 'id_ciudad' ? '' : 'd-none' }}">
                                    <option value="">-- Seleccione Ciudad --</option>
                                    @foreach($ciudades as $ciu)
                                        <option value="{{ trim($ciu->id_ciudad) }}" {{ request('valor_ciudad') == trim($ciu->id_ciudad) ? 'selected' : '' }}>
                                            {{ $ciu->ciu_descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-concho btn-sm fw-bold px-4 w-100 shadow-sm">Buscar</button>
                                    <button type="button" class="btn btn-secondary btn-sm fw-bold px-3 shadow-sm" data-bs-toggle="collapse" data-bs-target="#panelBusqueda">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- TABLA DE RESULTADOS --}}
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

        {{-- PAGINACIÓN --}}
        <div class="d-flex justify-content-center mt-3 mb-5">
            {{ $clientes->appends(request()->all())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    {{-- SCRIPTS JS --}}
    <script>
        document.getElementById('criterioBusqueda').addEventListener('change', function() {
            const ph = this.options[this.selectedIndex].getAttribute('data-ph');
            document.getElementById('labelDinamico').innerText = ph;

            if (this.value === 'id_ciudad') {
                document.getElementById('inputTexto').classList.add('d-none');
                document.getElementById('selectCiudad').classList.remove('d-none');
            } else {
                document.getElementById('selectCiudad').classList.add('d-none');
                document.getElementById('inputTexto').classList.remove('d-none');
            }
        });
    </script>
@endsection
