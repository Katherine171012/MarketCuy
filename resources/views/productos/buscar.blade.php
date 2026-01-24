<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        Consulta por parámetro
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('productos.buscar') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Orden</label>
                    <select name="orden" class="form-select">
                        <option value="">Seleccione</option>
                        <option value="id_asc"  {{ request('orden') === 'id_asc' ? 'selected' : '' }}>ID (Asc)</option>
                        <option value="id_desc" {{ request('orden') === 'id_desc' ? 'selected' : '' }}>ID (Desc)</option>
                        <option value="desc_az" {{ request('orden') === 'desc_az' ? 'selected' : '' }}>Descripción (A-Z)</option>
                        <option value="desc_za" {{ request('orden') === 'desc_za' ? 'selected' : '' }}>Descripción (Z-A)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select name="id_categoria" class="form-select">
                        <option value="">Todas</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id_categoria }}"
                                {{ (string)request('id_categoria') === (string)$c->id_categoria ? 'selected' : '' }}>
                                {{ $c->cat_nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unidad de medida</label>
                    <select name="unidad_medida" class="form-select">
                        <option value="">Todas</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u->id_unidad_medida }}"
                                {{ (string)request('unidad_medida') === (string)$u->id_unidad_medida ? 'selected' : '' }}>
                                {{ $u->id_unidad_medida }} - {{ $u->um_descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Registros por página</label>
                    <select name="per_page" class="form-select">
                        <option value="10"  {{ (string)request('per_page', '10') === '10' ? 'selected' : '' }}>10</option>
                        <option value="25"  {{ (string)request('per_page') === '25' ? 'selected' : '' }}>25</option>
                        <option value="50"  {{ (string)request('per_page') === '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ (string)request('per_page') === '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-md-8 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">
                        Buscar
                    </button>

                    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>
