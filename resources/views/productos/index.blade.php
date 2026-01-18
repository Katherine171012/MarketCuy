@extends('layouts.app')

@section('titulo', 'MarketCuy')

@section('contenido')
    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($info) && $info)
        <div class="alert alert-warning mb-3">{{ $info }}</div>
    @endif

    @php
        $catMap = [];
        if (isset($categorias) && $categorias) {
            foreach ($categorias as $c) {
                $catMap[$c->id_categoria] = $c->cat_nombre;
            }
        }
    @endphp

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="h4 mb-0">Módulo Productos</h2>

        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary"
               href="{{ route('productos.index', ['create' => 1]) }}">
                + Crear nuevo producto
            </a>

            <button class="btn btn-outline-dark"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseBuscar">
                Consulta por parámetro
            </button>
        </div>
    </div>

    @if(request('create'))
        <div class="mb-3">
            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                ← Volver
            </a>
        </div>

        <div class="row g-3">
            <div class="col-lg-8 col-xl-6">
                @include('productos.create')
            </div>
        </div>

    @else

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="collapse" id="collapseBuscar">
                    @include('productos.buscar')
                </div>
            </div>
        </div>

        @if(isset($productoEditar) && $productoEditar)
            <div class="mb-4">
                @include('productos.edit')
            </div>
        @endif

        @if(isset($productoVer) && $productoVer)
            @php
                $catVer = $catMap[$productoVer->id_categoria] ?? $productoVer->id_categoria;
                $imgUrlVer = !empty($productoVer->pro_imagen) ? asset('storage/' . ltrim($productoVer->pro_imagen,'/')) : null;
            @endphp

            <div class="card mb-4">
                <div class="card-header fw-semibold">
                    Detalle del Producto: {{ $productoVer->id_producto }}
                </div>

                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4"><strong>ID:</strong> {{ $productoVer->id_producto }}</div>
                        <div class="col-md-8"><strong>Nombre:</strong> {{ $productoVer->pro_nombre }}</div>

                        <div class="col-md-12">
                            <strong>Descripción:</strong>
                            {{ $productoVer->pro_descripcion ?? '—' }}
                        </div>

                        <div class="col-md-4"><strong>Categoría:</strong> {{ $catVer }}</div>

                        <div class="col-md-4">
                            <strong>Precio:</strong>
                            @if(!is_null($productoVer->pro_precio_antes))
                                <span class="text-muted text-decoration-line-through me-2">{{ $productoVer->pro_precio_antes }}</span>
                            @endif
                            <span class="fw-semibold">{{ $productoVer->pro_precio_venta }}</span>
                        </div>

                        <div class="col-md-4"><strong>Stock:</strong> {{ $productoVer->pro_saldo_final }}</div>

                        <div class="col-md-4"><strong>UM:</strong> {{ $productoVer->pro_um_compra }}</div>

                        <div class="col-md-4">
                            <strong>Etiqueta:</strong> {{ $productoVer->pro_etiqueta ?? '—' }}
                        </div>

                        <div class="col-md-4">
                            <strong>Destacado:</strong>
                            {{ $productoVer->pro_es_destacado ? 'Sí' : 'No' }}
                        </div>

                        @if($imgUrlVer)
                            <div class="col-12">
                                <div class="p-2 border rounded bg-light d-flex align-items-center gap-3">
                                    <img src="{{ $imgUrlVer }}" alt="Imagen" style="width:84px;height:84px;object-fit:cover;border-radius:12px;">
                                    <div class="small text-muted">
                                        {{ $productoVer->pro_imagen }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cerrar</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th class="text-center" style="width: 240px;">Acciones</th>
                </tr>
                </thead>

                <tbody>
                @foreach($productos as $p)
                    @php
                        [$badge, $estadoTxt] = match($p->estado_prod) {
                            'ACT' => ['success', 'Activo'],
                            'INA' => ['secondary', 'Inactivo'],
                            default => ['secondary', $p->estado_prod ?? 'Desconocido'],
                        };

                        $catTxt = $catMap[$p->id_categoria] ?? $p->id_categoria;

                        $imgUrl = !empty($p->pro_imagen) ? asset('storage/' . ltrim($p->pro_imagen,'/')) : null;
                    @endphp

                    <tr>
                        <td class="fw-semibold">{{ $p->id_producto }}</td>

                        <td>
                            <div>
                                <div class="fw-semibold">
                                    {{ $p->pro_nombre }}
                                    @if($p->pro_es_destacado)
                                        <span class="ms-1 badge bg-warning text-dark">Destacado</span>
                                    @endif
                                </div>

                                @if(!empty($p->pro_etiqueta))
                                    <div class="small">
                                        <span class="badge bg-info text-dark">{{ $p->pro_etiqueta }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <td>{{ $catTxt }}</td>

                        <td>
                            @if(!is_null($p->pro_precio_antes))
                                <div class="small text-muted text-decoration-line-through">
                                    {{ $p->pro_precio_antes }}
                                </div>
                            @endif
                            <div class="fw-semibold">{{ $p->pro_precio_venta }}</div>
                        </td>

                        <td>{{ $p->pro_saldo_final }}</td>

                        <td>
                            <span class="badge bg-{{ $badge }}">{{ $estadoTxt }}</span>
                        </td>

                        <td class="text-center">
                            @if($p->estado_prod === 'ACT')
                                @php($modalId = 'modalEliminar_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $p->id_producto))

                                <a class="btn btn-warning btn-sm"
                                   href="{{ route('productos.index', ['edit' => $p->id_producto]) }}">
                                    Editar
                                </a>

                                <button type="button"
                                        class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#{{ $modalId }}">
                                    Eliminar
                                </button>

                                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmar eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>

                                            <div class="modal-body">
                                                ¿Estás seguro de eliminar el producto
                                                <strong>{{ $p->id_producto }}</strong> – <strong>{{ $p->pro_nombre }}</strong>?
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Cancelar
                                                </button>

                                                <form method="POST" action="{{ route('productos.destroy', $p->id_producto) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-danger">
                                                        Sí, eliminar
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @else
                                <a class="btn btn-outline-dark btn-sm"
                                   href="{{ route('productos.index', ['view' => $p->id_producto]) }}">
                                    Visualizar
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if($productos->count() === 0)
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Sin registros
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2 mt-3">
            <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
                <label class="small text-muted">Mostrar</label>

                @foreach(request()->except(['page','per_page']) as $k => $v)
                    @if(is_array($v))
                        @foreach($v as $vv)
                            <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach

                <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="10"  {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25"  {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50"  {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>

                <span class="small text-muted">registros</span>
            </form>

            <div class="flex-grow-1 text-center">
                @if($productos->total() > 0)
                    <span class="small text-muted fw-bold">
                        Mostrando {{ $productos->lastItem() }} de {{ $productos->total() }} registros
                    </span>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $productos->links('pagination::bootstrap-4') }}
        </div>

    @endif
@endsection
