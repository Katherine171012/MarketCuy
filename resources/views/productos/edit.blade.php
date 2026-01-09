<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold text-white" style="background:#660404;">
        Editar producto: {{ $productoEditar->id_producto }}
    </div>

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
                    <label class="form-label">Descripción</label>
                    <input type="text" class="form-control" value="{{ $productoEditar->pro_descripcion }}" disabled>
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

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select name="pro_categoria" class="form-select" required>
                        <option value="">Seleccione categoría</option>
                        <option value="Alimentos"  {{ $productoEditar->pro_categoria == 'Alimentos' ? 'selected' : '' }}>Alimentos</option>
                        <option value="Medicinas"  {{ $productoEditar->pro_categoria == 'Medicinas' ? 'selected' : '' }}>Medicinas</option>
                        <option value="Ropa"       {{ $productoEditar->pro_categoria == 'Ropa' ? 'selected' : '' }}>Ropa</option>
                        <option value="Otros"      {{ $productoEditar->pro_categoria == 'Otros' ? 'selected' : '' }}>Otros</option>
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
                    <label class="form-label">Precio venta</label>
                    <input type="number" step="0.01" min="0"
                           name="pro_precio_venta"
                           class="form-control"
                           value="{{ old('pro_precio_venta', $productoEditar->pro_precio_venta) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock inicial</label>
                    <input type="number" class="form-control"
                           value="{{ $productoEditar->pro_saldo_inicial }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ingresos</label>
                    <input type="number" class="form-control"
                           value="{{ $productoEditar->pro_qty_ingresos }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Egresos</label>
                    <input type="number" class="form-control"
                           value="{{ $productoEditar->pro_qty_egresos }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock final </label>
                    <input type="number" class="form-control"
                           value="{{ $productoEditar->pro_saldo_final }}" disabled>
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
