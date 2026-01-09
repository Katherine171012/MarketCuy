@extends('layouts.app')
@section('titulo', 'Proveedores')
@section('contenido')
    <div class="row align-items-center mb-3">
        <div class="col">
            <h5 class="fw-bold mb-0 text-dark">Módulo Proveedores</h5>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('proveedores.create') }}"
               class="btn btn-primary btn-sm fw-bold px-3 py-2 shadow-sm">
                + Crear nuevo proveedor
            </a>

            <a href="#busqueda"
               class="btn btn-outline-secondary btn-sm fw-bold px-3 py-2 bg-white"
               style="color: #660404; border-color: #660404;"
               onclick="toggleBusqueda();">
                Consulta por parámetro
            </a>
        </div>
    </div>

    @if(isset($proveedorEdit) && $proveedorEdit)
        @include('proveedores.edit', ['proveedor' => $proveedorEdit, 'ciudades' => $ciudades, 'modo' => 'edit'])
    @endif

    @if(isset($proveedorView) && $proveedorView)
        @include('proveedores.edit', ['proveedor' => $proveedorView, 'ciudades' => $ciudades, 'modo' => 'view'])
    @endif

    @if(isset($proveedorDelete) && $proveedorDelete)
        @include('proveedores.eliminar', ['proveedor' => $proveedorDelete])
    @endif
    @php
        $busquedaActiva = request('parametro') || request('valor') || request('orden');
    @endphp
    <div class="row mb-3" id="busqueda" style="{{ $busquedaActiva ? '' : 'display:none;' }}">
        <div class="col-md-12">
            <form method="GET" action="{{ route('proveedores.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Ordenar por</label>
                    <select name="orden" class="form-select form-select-sm">
                        <option value="">Seleccione orden</option>
                        <option value="nombre" {{ request('orden')=='nombre' ? 'selected' : '' }}>Nombre</option>
                        <option value="estado" {{ request('orden')=='estado' ? 'selected' : '' }}>Estado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Parámetro</label>
                    <select name="parametro" class="form-select form-select-sm">
                        <option value="">Seleccione parámetro</option>
                        <option value="nombre" {{ request('parametro')=='nombre' ? 'selected' : '' }}>Nombre</option>
                        <option value="ruc" {{ request('parametro')=='ruc' ? 'selected' : '' }}>RUC/Cédula</option>
                        <option value="correo" {{ request('parametro')=='correo' ? 'selected' : '' }}>Correo</option>
                        <option value="estado" {{ request('parametro')=='estado' ? 'selected' : '' }}>Estado</option>
                        <option value="ciudad" {{ request('parametro')=='ciudad' ? 'selected' : '' }}>Ciudad</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Valor</label>
                    <input type="text"
                           name="valor"
                           value="{{ request('valor') }}"
                           class="form-control form-control-sm"
                           placeholder="Ingrese valor">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary btn-sm fw-bold px-4">
                        Buscar
                    </button>

                    <a href="{{ route('proveedores.index') }}"
                       class="btn btn-outline-secondary btn-sm fw-bold px-4"
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
                    <th class="px-3">ID</th>
                    <th>Nombre</th>
                    <th>RUC/Cédula</th>
                    <th>Ciudad</th>
                    <th>Correo</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center" style="width: 180px;">Acciones</th>
                </tr>
                </thead>
                <tbody class="bg-white">
                @foreach($proveedores as $prv)
                    @php
                        $idLimpio = trim($prv->id_proveedor);
                        $activo = trim($prv->estado_prv) === 'ACT';
                    @endphp
                    <tr style="{{ !$activo ? 'background-color:#f9f9f9;color:#aaa;' : '' }}">
                        <td class="px-3 fw-bold text-secondary">{{ $prv->id_proveedor }}</td>
                        <td class="fw-bold text-dark">{{ $prv->prv_nombre }}</td>
                        <td>{{ $prv->prv_ruc_ced }}</td>
                        <td>{{ $prv->ciudad->ciu_descripcion ?? 'N/A' }}</td>
                        <td>{{ $prv->prv_mail }}</td>
                        <td class="text-center">
                            @if($activo)
                                <span class="badge rounded-pill bg-success px-3">Activo</span>
                            @else
                                <span class="badge rounded-pill bg-secondary opacity-50 px-3">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($activo)
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- EDITAR en el MISMO INDEX (panel arriba) --}}
                                    <a href="{{ route('proveedores.index', array_merge(request()->query(), ['edit' => $idLimpio, 'view' => null, 'delete' => null])) }}"
                                       class="btn btn-sm fw-bold px-3 py-1"
                                       style="background-color:#ffc107;border:none;font-size:.75rem;">
                                        Editar
                                    </a>
                                    <a href="{{ route('proveedores.index', array_merge(request()->query(), ['delete' => $idLimpio, 'edit' => null, 'view' => null])) }}"
                                       class="btn btn-danger btn-sm fw-bold px-3 py-1 shadow-sm"
                                       style="border:none;font-size:.75rem;">
                                        Eliminar
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('proveedores.index', array_merge(request()->query(), ['view' => $idLimpio, 'edit' => null, 'delete' => null])) }}"
                                   class="text-muted small fw-bold text-decoration-none">
                                    Visualizar
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if($proveedores->count() === 0)
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
    <form method="GET" action="{{ route('proveedores.index') }}" class="d-flex align-items-center gap-2">
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
            @if($proveedores->total() > 0)
                <span class="small text-muted fw-bold">
                    Mostrando {{ $proveedores->lastItem() }} de {{ $proveedores->total() }} registros
                </span>
            @endif
        </div>
    </form>
    <div class="d-flex justify-content-center mt-4">
        {{ $proveedores->links('pagination::bootstrap-4') }}
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
