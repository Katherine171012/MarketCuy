<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold text-white" style="background:#660404;">
        Editar producto: {{ $productoEditar->id_producto }}
    </div>

    @php
        $imgUrl = null;
        if (!empty($productoEditar->pro_imagen)) {
            $imgUrl = asset('storage/' . ltrim($productoEditar->pro_imagen, '/'));
        }

        $etqVal = trim((string) old('pro_etiqueta', $productoEditar->pro_etiqueta));
        $etqValLower = mb_strtolower($etqVal);
    @endphp

    <div class="card-body">
        <form method="POST" action="{{ route('productos.update', $productoEditar->id_producto) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">ID</label>
                    <input type="text" class="form-control" value="{{ $productoEditar->id_producto }}" disabled>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" value="{{ $productoEditar->pro_nombre }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Unidad de medida (compra)</label>
                    @php
                        $uc = $unidades->firstWhere('id_unidad_medida', $productoEditar->pro_um_compra);
                        $ucTxt = $uc ? ($uc->id_unidad_medida . ' - ' . ($uc->um_descripcion ?? '')) : $productoEditar->pro_um_compra;
                    @endphp
                    <input type="text" class="form-control" value="{{ $ucTxt }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Unidad de medida (venta)</label>
                    @php
                        $uv = $unidades->firstWhere('id_unidad_medida', $productoEditar->pro_um_venta);
                        $uvTxt = $uv ? ($uv->id_unidad_medida . ' - ' . ($uv->um_descripcion ?? '')) : $productoEditar->pro_um_venta;
                    @endphp
                    <input type="text" class="form-control" value="{{ $uvTxt }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    @php
                        $estadoTxt = match($productoEditar->estado_prod) {
                            'ACT' => 'Activo',
                            'INA' => 'Inactivo',
                            'PEN' => 'Pendiente',
                            default => $productoEditar->estado_prod ?? 'Desconocido',
                        };
                    @endphp
                    <input type="text" class="form-control" value="{{ $estadoTxt }}" disabled>
                </div>
            </div>

            <hr class="my-4 text-muted">

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <input type="text"
                       name="pro_descripcion"
                       class="form-control"
                       value="{{ old('pro_descripcion', $productoEditar->pro_descripcion) }}">
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Precio antes</label>
                    <input type="number" step="0.01" min="0"
                           class="form-control"
                           value="{{ number_format((float)$productoEditar->pro_precio_venta, 2, '.', '') }}"
                           disabled>
                    <div class="form-text">
                        Para "Oferta", el nuevo precio de venta debe ser menor a este valor.
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Precio venta</label>
                    <input type="number" step="0.01" min="0"
                           name="pro_precio_venta"
                           class="form-control"
                           value="{{ old('pro_precio_venta', $productoEditar->pro_precio_venta) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Etiqueta</label>
                    <select class="form-select" name="pro_etiqueta">
                        <option value="" {{ $etqValLower === '' ? 'selected' : '' }}>Sin etiqueta</option>
                        <option value="Oferta" {{ $etqValLower === 'oferta' ? 'selected' : '' }}>Oferta</option>
                        <option value="Más vendido" {{ $etqValLower === mb_strtolower('Más vendido') ? 'selected' : '' }}>Más vendido</option>
                        <option value="Recomendado" {{ $etqValLower === 'recomendado' ? 'selected' : '' }}>Recomendado</option>
                        <option value="Edición limitada" {{ $etqValLower === mb_strtolower('Edición limitada') ? 'selected' : '' }}>Edición limitada</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-check mt-1">
                        <input class="form-check-input"
                               type="checkbox"
                               name="pro_es_destacado"
                               id="pro_es_destacado"
                            {{ old('pro_es_destacado', (bool)$productoEditar->pro_es_destacado) ? 'checked' : '' }}>
                        <label class="form-check-label" for="pro_es_destacado">
                            Producto destacado (aparece en portada)
                        </label>
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select name="id_categoria" class="form-select" required>
                        <option value="">Seleccione categoría</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id_categoria }}"
                                {{ (int)$productoEditar->id_categoria === (int)$c->id_categoria ? 'selected' : '' }}>
                                {{ $c->cat_nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Precio compra</label>
                    <input type="number" step="0.01" min="0"
                           name="pro_valor_compra"
                           class="form-control"
                           value="{{ old('pro_valor_compra', $productoEditar->pro_valor_compra) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cambiar foto (opcional)</label>
                    <input type="file"
                           class="form-control"
                           name="pro_imagen"
                           accept=".jpg,.jpeg,.pdf">
                    <div class="form-text">Solo se permiten JPG o PDF.</div>
                </div>

                @if($imgUrl)
                    <div class="col-12">
                        <div class="p-2 border rounded bg-light d-flex align-items-center gap-3">
                            <img src="{{ $imgUrl }}" alt="Imagen actual" style="width:72px;height:72px;object-fit:cover;border-radius:10px;">
                            <div class="small text-muted">
                                Imagen actual: <span class="fw-semibold">{{ $productoEditar->pro_imagen }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <hr class="my-4 text-muted">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Stock inicial</label>
                    <input type="number" class="form-control" value="{{ $productoEditar->pro_saldo_inicial }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ingresos</label>
                    <input type="number" class="form-control" value="{{ $productoEditar->pro_qty_ingresos }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Egresos</label>
                    <input type="number" class="form-control" value="{{ $productoEditar->pro_qty_egresos }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ajustes</label>
                    <input type="number" class="form-control" value="{{ $productoEditar->pro_qty_ajustes }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock final</label>
                    <input type="number" class="form-control" value="{{ $productoEditar->pro_saldo_final }}" disabled>
                </div>
            </div>

            <input type="hidden" name="pro_saldo_inicial" value="{{ $productoEditar->pro_saldo_inicial }}">
            <input type="hidden" name="pro_qty_ingresos" value="{{ $productoEditar->pro_qty_ingresos }}">
            <input type="hidden" name="pro_qty_egresos" value="{{ $productoEditar->pro_qty_egresos }}">
            <input type="hidden" name="pro_qty_ajustes" value="{{ $productoEditar->pro_qty_ajustes }}">
            <input type="hidden" name="pro_saldo_final" value="{{ $productoEditar->pro_saldo_final }}">

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
                <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
