{{-- BUSCADOR POR PARÁMETROS (PARTIAL) --}}

<div class="alert bg-concho text-white">
    Ingrese al menos un parámetro. Puede combinar filtros.
</div>

<form method="GET" action="{{ route('facturas.index') }}" class="mb-4">

    <input type="hidden" name="mostrar_busqueda" value="1">

    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">N° Factura</label>
            <input type="text"
                   name="id_factura"
                   class="form-control"
                   value="{{ $busquedaActiva ? '' : request('id_factura') }}"
                   placeholder="FAC0001">
        </div>

        <div class="col-md-5">
            <label class="form-label">Cliente</label>
            <input type="text"
                   name="cliente"
                   class="form-control"
                   value="{{ $busquedaActiva ? '' : request('cliente') }}"
                   placeholder="Ej: Camila Lema">
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="estado_fac" class="form-select">
                <option value="">-- Todos --</option>
                <option value="ABI" {{ (!$busquedaActiva && request('estado_fac') === 'ABI') ? 'selected' : '' }}>
                    Abierta
                </option>
                <option value="APR" {{ (!$busquedaActiva && request('estado_fac') === 'APR') ? 'selected' : '' }}>
                    Aprobada
                </option>
                <option value="ANU" {{ (!$busquedaActiva && request('estado_fac') === 'ANU') ? 'selected' : '' }}>
                    Anulada
                </option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Fecha desde</label>
            <input type="date"
                   name="fecha_desde"
                   class="form-control"
                   value="{{ $busquedaActiva ? '' : request('fecha_desde') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Fecha hasta</label>
            <input type="date"
                   name="fecha_hasta"
                   class="form-control"
                   value="{{ $busquedaActiva ? '' : request('fecha_hasta') }}">
        </div>

    </div>

    <div class="mt-4 d-flex gap-2">
        <a href="{{ route('facturas.index') }}" class="btn btn-concho">
            Volver
        </a>

        <button type="submit" class="btn btn-concho">
            Buscar
        </button>
    </div>
</form>
