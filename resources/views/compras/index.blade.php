@extends('layouts.app')

@section('titulo', 'Compras')

@section('contenido')
    <div class="row align-items-center mb-3">
        <div class="col">
            <h5 class="fw-bold mb-0 text-dark">Módulo Compras</h5>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('compras.create') }}" class="btn btn-primary btn-sm fw-bold px-3 py-2 shadow-sm">
                + Generar Orden de Compra
            </a>

            <a href="#busqueda" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-2 bg-white"
                style="color: #660404; border-color: #660404;" onclick="toggleBusqueda();">
                Consulta por parámetro
            </a>
        </div>
    </div>

    {{-- MODALES tipo proveedores --}}
    @if(isset($compraDelete) && $compraDelete)
        @include('compras.eliminar', ['compra' => $compraDelete])
    @endif

    <div class="row mb-3" id="busqueda" style="{{ $busquedaActiva ? '' : 'display:none;' }}">
        <div class="col-md-12">
            <form method="GET" action="{{ route('compras.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Ordenar por</label>
                    <select name="orden" class="form-select form-select-sm">
                        <option value="">Seleccione orden</option>
                        <option value="fecha" {{ request('orden') == 'fecha' ? 'selected' : '' }}>Fecha</option>
                        <option value="estado" {{ request('orden') == 'estado' ? 'selected' : '' }}>Estado</option>
                        <option value="proveedor" {{ request('orden') == 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">Parámetro</label>
                    <select name="parametro" class="form-select form-select-sm">
                        <option value="">Seleccione parámetro</option>
                        <option value="id_compra" {{ request('parametro') == 'id_compra' ? 'selected' : '' }}>Código OC
                        </option>
                        <option value="id_proveedor" {{ request('parametro') == 'id_proveedor' ? 'selected' : '' }}>Proveedor
                        </option>
                        <option value="estado_oc" {{ request('parametro') == 'estado_oc' ? 'selected' : '' }}>Estado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">Valor</label>
                    <input type="text" name="valor" value="{{ request('valor') }}" class="form-control form-control-sm"
                        placeholder="Ingrese valor">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary btn-sm fw-bold px-4">
                        Buscar
                    </button>

                    <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary btn-sm fw-bold px-4"
                        style="color:#660404;border-color:#660404;">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3">OC</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 240px;">Acciones</th>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    @foreach($ordenes as $oc)
                        @php
                            // Usar métodos del modelo en lugar de lógica quemada
                            $idOc = $oc->getIdLimpio();
                            $esAbierta = $oc->esAbierta();
                            $esAprobada = $oc->esAprobada();
                            $esAnulada = $oc->esAnulada();
                            $estado = trim((string) $oc->estado_oc); // Solo para mostrar
                        @endphp

                        <tr style="{{ $esAnulada ? 'background-color:#f9f9f9;color:#aaa;' : '' }}">
                            <td class="px-3 fw-bold text-secondary">{{ $oc->id_compra }}</td>

                            {{-- Por ahora ID; luego lo cambiamos a nombre con join --}}
                            <td class="fw-bold text-dark">{{ $oc->id_proveedor }}</td>

                            <td>{{ $oc->oc_fecha_hora }}</td>

                            <td class="text-end">{{ number_format((float) $oc->oc_subtotal, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $oc->oc_total, 2) }}</td>

                            <td class="text-center">
                                @if($esAbierta)
                                    <span class="badge rounded-pill bg-warning text-dark px-3">Abierta</span>
                                @elseif($esAprobada)
                                    <span class="badge rounded-pill bg-success px-3">Aprobada</span>
                                @elseif($esAnulada)
                                    <span class="badge rounded-pill bg-secondary opacity-50 px-3">Anulada</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary px-3">{{ $estado }}</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($esAbierta)
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('compras.edit', $idOc) }}" class="btn btn-sm fw-bold px-3 py-1"
                                            style="background-color:#ffc107;border:none;font-size:.75rem;">
                                            Editar
                                        </a>

                                        <form method="POST" action="{{ route('compras.aprobar', $idOc) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm fw-bold px-3 py-1 shadow-sm"
                                                style="background-color:#198754;border:none;font-size:.75rem;color:#fff;">
                                                Aprobar
                                            </button>
                                        </form>

                                        <a href="{{ route('compras.index', array_merge(request()->query(), ['delete' => $idOc])) }}"
                                            class="btn btn-danger btn-sm fw-bold px-3 py-1 shadow-sm"
                                            style="border:none;font-size:.75rem;">
                                            Eliminar
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted small fw-bold">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @if($ordenes->count() === 0)
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No existen registros.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <br>

    <form method="GET" action="{{ route('compras.index') }}" class="d-flex align-items-center gap-2">
        @foreach(request()->except('per_page') as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach

        <label class="small text-muted">Mostrar</label>
        <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="small text-muted">registros</span>

        <div class="col-md-9 text-center">
            @if($ordenes->total() > 0)
                <span class="small text-muted fw-bold">
                    Mostrando {{ $ordenes->lastItem() }} de {{ $ordenes->total() }} registros
                </span>
            @endif
        </div>
    </form>

    <div class="d-flex justify-content-center mt-4">
        {{ $ordenes->links('pagination::bootstrap-4') }}
    </div>

    <script>
        function toggleBusqueda() {
            const panel = document.getElementById('busqueda');
            if (!panel) return;

            if (panel.style.display === 'none' || panel.style.display === '') {
                panel.style.display = 'block';
            } else {
                panel.style.display = 'none';
            }
        }
    </script>
@endsection
