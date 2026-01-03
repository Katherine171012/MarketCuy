@extends('layouts.app')

@section('titulo', 'Listado de Facturas')

@section('contenido')

    {{-- ENCABEZADO DEL MÓDULO --}}
    <div class="row align-items-center mb-3">
        <div class="col">
            <h5 class="fw-bold mb-0 text-dark">
                Listado de Facturas
            </h5>
        </div>

        <div class="col-auto d-flex gap-2">
            <a href="{{ route('facturas.create') }}"
               class="btn btn-primary btn-sm fw-bold px-3 py-2 shadow-sm">
                + Nueva Factura
            </a>

            <a href="{{ route('facturas.index', ['mostrar_busqueda' => 1]) }}"
               class="btn btn-outline-secondary btn-sm fw-bold px-3 py-2 shadow-sm">
                Consulta por parámetros
            </a>
        </div>
    </div>

    {{-- BUSCADOR POR PARÁMETROS --}}
    @if($busquedaActiva || request()->has('mostrar_busqueda'))
        @include('facturas.buscar')
    @endif

    {{-- TABLA --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                <tr>
                    <th class="text-center">
                        Número
                    </th>
                    <th>
                        Cliente
                    </th>
                    <th class="text-center">
                        Fecha
                    </th>
                    <th class="text-end">
                        Subtotal
                    </th>
                    <th class="text-end">
                        Total
                    </th>
                    <th class="text-center">
                        Estado
                    </th>
                    <th class="text-center" style="width: 180px;">
                        Acciones
                    </th>
                </tr>
                </thead>

                <tbody class="bg-white">
                @forelse($facturas as $factura)
                    <tr style="{{ $factura->estado_fac !== 'ABI' ? 'background-color:#f9f9f9;color:#999;' : '' }}">

                        <td class="text-center fw-bold text-secondary">
                            {{ $factura->id_factura }}
                        </td>

                        <td class="fw-bold text-dark">
                            {{ $factura->cli_nombre }}
                        </td>

                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($factura->fac_fecha_hora)->format('d/m/Y H:i') }}
                        </td>

                        <td class="text-end">
                            {{ number_format($factura->fac_subtotal, 2) }}
                        </td>

                        <td class="text-end fw-bold">
                            {{ number_format($factura->total, 2) }}
                        </td>

                        <td class="text-center">
                            @if($factura->estado_fac === 'ABI')
                                <span class="badge rounded-pill bg-warning text-dark px-3">
                                    Abierta
                                </span>
                            @elseif($factura->estado_fac === 'APR')
                                <span class="badge rounded-pill bg-success px-3">
                                    Aprobada
                                </span>
                            @else
                                <span class="badge rounded-pill bg-danger px-3">
                                    Anulada
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($factura->estado_fac === 'ABI')
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('facturas.edit', $factura->id_factura) }}"
                                       class="btn btn-warning btn-sm fw-bold px-3 py-1">
                                        Editar
                                    </a>

                                    <button type="button"
                                            class="btn btn-danger btn-sm fw-bold px-3 py-1"
                                            onclick="confirmarAnulacion('{{ $factura->id_factura }}')">
                                        Anular
                                    </button>
                                </div>
                            @else
                                <a href="{{ route('facturas.edit', $factura->id_factura) }}"
                                   class="text-muted small fw-bold text-decoration-none">
                                    Visualizar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No existen facturas registradas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form method="GET"
          action="{{ route('facturas.index') }}"
          class="d-flex align-items-center gap-2 mt-3">

        <label class="small text-muted">Mostrar</label>

        <select name="per_page"
                class="form-select form-select-sm w-auto"
                onchange="this.form.submit()">
            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>

        <span class="small text-muted">registros</span>

        <div class="flex-grow-1 text-center">
            @if($facturas->total() > 0)
                <span class="small text-muted fw-bold">
                Mostrando {{ $facturas->lastItem() }}
                de {{ $facturas->total() }} registros
            </span>
            @endif
        </div>
    </form>


    {{-- PAGINACIÓN --}}
    @if ($facturas->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $facturas->links('pagination::bootstrap-4') }}
        </div>
    @endif

    {{-- MODAL CONFIRMAR ANULACIÓN --}}
    <div class="modal fade" id="modalAnularFactura" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Confirmar anulación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    ¿Está seguro de anular la factura
                    <strong id="factura-anular-id"></strong>?
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <form id="formAnularFactura" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            Sí, anular
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function confirmarAnulacion(idFactura) {
            document.getElementById('factura-anular-id').textContent = idFactura;
            document.getElementById('formAnularFactura').action = `/facturas/${idFactura}/anular`;

            new bootstrap.Modal(
                document.getElementById('modalAnularFactura')
            ).show();
        }
    </script>

@endsection
