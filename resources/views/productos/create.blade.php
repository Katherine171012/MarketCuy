<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        Crear producto
    </div>

    <div class="card-body">
        <form method="POST"
              action="{{ route('productos.store') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text"
                           name="pro_nombre"
                           class="form-control"
                           value="{{ old('pro_nombre') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Categoría</label>
                    <select name="id_categoria" class="form-select">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id_categoria }}"
                                {{ (string)old('id_categoria') === (string)$c->id_categoria ? 'selected' : '' }}>
                                {{ $c->cat_nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="pro_descripcion"
                              class="form-control"
                              rows="2">{{ old('pro_descripcion') }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unidad de medida</label>
                    <select name="unidad_medida" class="form-select">
                        @foreach($unidades as $u)
                            <option value="{{ $u->id_unidad_medida }}"
                                {{ (string)old('unidad_medida') === (string)$u->id_unidad_medida ? 'selected' : '' }}>
                                {{ $u->id_unidad_medida }} - {{ $u->um_descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Valor compra</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="pro_valor_compra"
                           class="form-control"
                           value="{{ old('pro_valor_compra', 0) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Precio venta</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="pro_precio_venta"
                           class="form-control"
                           value="{{ old('pro_precio_venta') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock inicial</label>
                    <input type="number"
                           min="0"
                           name="pro_saldo_inicial"
                           class="form-control"
                           value="{{ old('pro_saldo_inicial', 0) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Imagen</label>
                    <input type="file"
                           name="pro_imagen"
                           class="form-control">
                </div>

            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Guardar
                </button>

                <a href="{{ route('productos.index') }}"
                   class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
</div>
