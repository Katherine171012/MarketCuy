<div class="card">
    <div class="card-header fw-semibold">
        Búsqueda avanzada
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('productos.buscar') }}">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Ordenar resultados</label>
                    <select name="orden" class="form-select">
                        <option value="">Seleccione orden</option>
                        <option value="id_asc"  {{ request('orden')=='id_asc' ? 'selected' : '' }}>ID (A-Z)</option>
                        <option value="id_desc" {{ request('orden')=='id_desc' ? 'selected' : '' }}>ID (Z-A)</option>
                        <option value="desc_az" {{ request('orden')=='desc_az' ? 'selected' : '' }}>Descripción (A-Z)</option>
                        <option value="desc_za" {{ request('orden')=='desc_za' ? 'selected' : '' }}>Descripción (Z-A)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select name="pro_categoria" class="form-select">
                        <option value="">(Opcional) Todas</option>
                        <option value="Alimentos" {{ request('pro_categoria')=='Alimentos' ? 'selected' : '' }}>Alimentos</option>
                        <option value="Medicinas" {{ request('pro_categoria')=='Medicinas' ? 'selected' : '' }}>Medicinas</option>
                        <option value="Ropa"      {{ request('pro_categoria')=='Ropa' ? 'selected' : '' }}>Ropa</option>
                        <option value="Otros"     {{ request('pro_categoria')=='Otros' ? 'selected' : '' }}>Otros</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unidad de medida</label>
                    <select name="unidad_medida" class="form-select">
                        <option value="">(Opcional) Todas</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u->id_unidad_medida }}"
                                {{ request('unidad_medida') == $u->id_unidad_medida ? 'selected' : '' }}>
                                {{ $u->id_unidad_medida }} - {{ $u->um_descripcion ?? 'Sin descripción' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-dark">
                    Buscar
                </button>

                <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                    Limpiar
                </a>
            </div>

            <div class="form-text mt-2">
                Puedes filtrar por 1 o varios criterios.
            </div>

        </form>
    </div>
</div>
