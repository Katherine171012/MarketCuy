<div class="card">
    <div class="card-header fw-semibold">
        Nuevo Producto
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <input type="text"
                       class="form-control"
                       name="pro_descripcion"
                       value="{{ old('pro_descripcion') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <select class="form-select" name="pro_categoria" required>
                    <option value="">Seleccione categoría</option>
                    <option value="Alimentos" {{ old('pro_categoria')=='Alimentos' ? 'selected' : '' }}>Alimentos</option>
                    <option value="Medicinas" {{ old('pro_categoria')=='Medicinas' ? 'selected' : '' }}>Medicinas</option>
                    <option value="Ropa"      {{ old('pro_categoria')=='Ropa' ? 'selected' : '' }}>Ropa</option>
                    <option value="Otros"     {{ old('pro_categoria')=='Otros' ? 'selected' : '' }}>Otros</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Unidad de Medida</label>
                <select class="form-select" name="unidad_medida" required>
                    <option value="">Seleccione unidad de medida</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id_unidad_medida }}"
                            {{ old('unidad_medida') == $u->id_unidad_medida ? 'selected' : '' }}>
                            {{ $u->id_unidad_medida }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Precio Compra</label>
                    <input type="number" step="0.01" min="0"
                           class="form-control"
                           name="pro_valor_compra"
                           value="{{ old('pro_valor_compra') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Precio Venta</label>
                    <input type="number" step="0.01" min="0"
                           class="form-control"
                           name="pro_precio_venta"
                           value="{{ old('pro_precio_venta') }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cantidad Inicial</label>
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
