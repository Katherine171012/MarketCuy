<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold text-white" style="background:#660404;">
    Editando Producto: {{ $productoEditar->id_producto }}
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('productos.update', $productoEditar->id_producto) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">ID Producto</label>
                    <input type="text" class="form-control" value="{{ $productoEditar->id_producto }}" disabled>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Nombre / Descripción</label>
                    <input type="text" class="form-control" value="{{ $productoEditar->pro_descripcion }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">UM Compra</label>
                    <input type="text" class="form-control" value="{{ $productoEditar->pro_um_compra }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">UM Venta</label>
                    <input type="text" class="form-control" value="{{ $productoEditar->pro_um_venta }}" disabled>
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
                    <label class="form-label">Precio Compra</label>
                    <input type="number" step="0.01" min="0"
                           name="pro_valor_compra"
                           class="form-control"
                           value="{{ old('pro_valor_compra', $productoEditar->pro_valor_compra) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Precio Venta</label>
                    <input type="number" step="0.01" min="0"
                           name="pro_precio_venta"
                           class="form-control"
                           value="{{ old('pro_precio_venta', $productoEditar->pro_precio_venta) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Saldo Inicial</label>
                    <input type="number" min="0"
                           name="pro_saldo_inicial"
                           class="form-control"
                           value="{{ old('pro_saldo_inicial', $productoEditar->pro_saldo_inicial) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Qty Ingresos</label>
                    <input type="number" min="0"
                           name="pro_qty_ingresos"
                           class="form-control"
                           value="{{ old('pro_qty_ingresos', $productoEditar->pro_qty_ingresos) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Qty Egresos</label>
                    <input type="number" min="0"
                           name="pro_qty_egresos"
                           class="form-control"
                           value="{{ old('pro_qty_egresos', $productoEditar->pro_qty_egresos) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Qty Ajustes</label>
                    <input type="number" min="0"
                           name="pro_qty_ajustes"
                           class="form-control"
                           value="{{ old('pro_qty_ajustes', $productoEditar->pro_qty_ajustes) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Saldo Final</label>
                    <input type="number" min="0"
                           name="pro_saldo_final"
                           class="form-control"
                           value="{{ old('pro_saldo_final', $productoEditar->pro_saldo_final) }}"
                           required>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Guardar Cambios
                </button>
                <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
