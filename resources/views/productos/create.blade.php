<div class="card">
    <div class="card-header fw-semibold">
        Nuevo producto
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text"
                       class="form-control"
                       name="pro_nombre"
                       value="{{ old('pro_nombre') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <input type="text"
                       class="form-control"
                       name="pro_descripcion"
                       value="{{ old('pro_descripcion') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <select class="form-select" name="id_categoria" required>
                    <option value="">Seleccione categoría</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id_categoria }}"
                            {{ old('id_categoria') == $c->id_categoria ? 'selected' : '' }}>
                            {{ $c->cat_nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Unidad de medida</label>
                <select class="form-select" name="unidad_medida" required>
                    <option value="">Seleccione unidad de medida</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id_unidad_medida }}"
                            {{ old('unidad_medida') == $u->id_unidad_medida ? 'selected' : '' }}>
                            {{ $u->id_unidad_medida }} - {{ $u->um_descripcion ?? 'Sin descripción' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Etiqueta (promo)</label>
                <input type="text"
                       class="form-control"
                       name="pro_etiqueta"
                       value="{{ old('pro_etiqueta') }}"
                       placeholder="Ej: oferta, descuento %, promo especial...">
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input"
                       type="checkbox"
                       name="pro_es_destacado"
                       id="pro_es_destacado"
                    {{ old('pro_es_destacado') ? 'checked' : '' }}>
                <label class="form-check-label" for="pro_es_destacado">
                    Producto destacado (aparece en portada)
                </label>
            </div>

            <div class="mb-3">
                <label class="form-label">Imagen del producto (opcional)</label>
                <input type="file"
                       class="form-control"
                       name="pro_imagen"
                       accept=".jpg,.jpeg,.pdf">
                <div class="form-text">
                    Solo se permiten archivos JPG o PDF.
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Precio compra</label>
                    <input type="number" step="0.01" min="0"
                           class="form-control"
                           name="pro_valor_compra"
                           value="{{ old('pro_valor_compra') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Precio venta</label>
                    <input type="number" step="0.01" min="0"
                           class="form-control"
                           name="pro_precio_venta"
                           value="{{ old('pro_precio_venta') }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Precio antes</label>
                    <input type="number" step="0.01" min="0"
                           class="form-control"
                           name="pro_precio_antes"
                           value="{{ old('pro_precio_antes') }}"
                           placeholder="Obligatorio si etiqueta es Oferta">
                    <div class="form-text">Si se llena, debe ser mayor al precio de venta.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Stock inicial</label>
                    <input type="number" min="0"
                           class="form-control"
                           name="pro_saldo_inicial"
                           value="{{ old('pro_saldo_inicial') }}"
                           required>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary" type="submit">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
