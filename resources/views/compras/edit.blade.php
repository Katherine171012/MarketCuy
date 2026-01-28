@extends('layouts.app')

@section('titulo', 'Editar Orden de Compra')

@section('contenido')

    <h1 class="mb-3">Editar Orden de Compra</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('compras.update', $compra->getIdLimpio()) }}"
          method="POST"
          id="formEditarOC">
        @csrf
        @method('PUT')

        {{-- CABECERA --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3 text-uppercase"
                    style="color:#660404;border-bottom:2px solid #660404;padding-bottom:6px;">
                    DATOS DE LA ORDEN
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ID Orden</label>
                        <input class="form-control"
                               value="{{ $compra->getIdLimpio() }}"
                               disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha/Hora</label>
                        <input class="form-control"
                               value="{{ $compra->oc_fecha_hora }}"
                               disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <input class="form-control"
                               value="{{ trim($compra->estado_oc) }}"
                               disabled>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">
                            Proveedor <span class="text-danger">*</span>
                        </label>

                        <select name="id_proveedor"
                                id="selectProveedor"
                                class="form-select @error('id_proveedor') is-invalid @enderror">
                            <option value="">Seleccione un proveedor</option>
                        @foreach($proveedores as $prv)
                                <option value="{{ $prv->getIdLimpio() }}"
                                    {{ old('id_proveedor', $compra->getIdProveedorLimpio()) === $prv->getIdLimpio() ? 'selected' : '' }}>
                                    {{ $prv->getNombreLimpio() }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_proveedor')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- DETALLE --}}
        <h4 class="mb-2" style="color:#660404;">
            Detalle de Productos
        </h4>

        <div class="table-responsive mb-3">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="width:42%">Producto</th>
                    <th class="text-end">Valor</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-center">Acción</th>
                </tr>
                </thead>

                <tbody id="contenedor-productos">
                {{-- $items ya viene preparado del controller --}}
                @foreach($items as $i => $item)
                    <tr class="producto-item">
                        <td>
                            <select name="productos[{{ $i }}][id_producto]"
                                    class="form-select form-select-sm select-producto"
                                    onchange="actualizarPrecio(this)">
                                <option value="">Seleccione un producto</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->getIdLimpio() }}"
                                            data-precio="{{ $p->pro_valor_compra ?? 0 }}"
                                        {{ trim($item['id_producto']) === $p->getIdLimpio() ? 'selected' : '' }}>
                                        {{ $p->getNombreLimpio() }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="text-end"><span class="precio">0.00</span></td>

                        <td>
                            <input type="number"
                                   name="productos[{{ $i }}][cantidad]"
                                   class="form-control form-control-sm text-center cantidad"
                                   min="1"
                                   value="{{ $item['cantidad'] }}"
                                   onkeydown="return soloFlechasCantidad(event);"
                                   oninput="actualizarSubtotal(this)">
                        </td>

                        <td class="text-end">
                            <strong class="subtotal">0.00</strong>
                        </td>

                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="eliminarProducto(this)">
                                Quitar
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <button type="button"
                class="btn btn-concho btn-sm mb-3"
                onclick="agregarProducto()">
            + Agregar producto
        </button>

        {{-- RESUMEN --}}
        <div class="row">
            <div class="col-md-5">
                <table class="table table-bordered">
                    <tr>
                        <th>Subtotal</th>
                        <td class="text-end" id="subtotal-general">0.00</td>
                    </tr>
                    <tr>
                        <th>IVA ({{ (int)($compra->oc_iva ?? 15) }}%)</th>
                        <td class="text-end" id="iva-general">0.00</td>
                    </tr>
                    <tr>
                        <th>TOTAL</th>
                        <td class="text-end">
                            <strong id="total-general">0.00</strong>
                        </td>
                    </tr>
                </table>

                <input type="hidden" name="accion" id="accionGuardar" value="guardar">

                <div class="d-flex gap-2">
                    <a href="{{ route('compras.index') }}"
                       class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn btn-primary"
                            onclick="document.getElementById('accionGuardar').value='guardar'">
                        Guardar
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            onclick="document.getElementById('accionGuardar').value='aprobar'">
                        Guardar y aprobar
                    </button>
                </div>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        const IVA_PORCENTAJE = {{ (int)($compra->oc_iva ?? 15) }};
        let indexOC = {{ count($items) }};

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar valores de filas existentes
            document.querySelectorAll('.select-producto').forEach(function(select) {
                actualizarPrecio(select);
            });
        });

        function agregarProducto() {
            const tbody = document.getElementById('contenedor-productos');
            const tr = document.createElement('tr');
            tr.className = 'producto-item';

            tr.innerHTML = `
                <td>
                    <select name="productos[${indexOC}][id_producto]"
                            class="form-select form-select-sm select-producto"
                            onchange="actualizarPrecio(this)">
                        <option value="">Seleccione un producto</option>
                        @foreach($productos as $p)
            <option value="{{ $p->getIdLimpio() }}"
                                    data-precio="{{ $p->pro_valor_compra ?? 0 }}">
                                {{ $p->getNombreLimpio() }}
            </option>
@endforeach
            </select>
        </td>
        <td class="text-end"><span class="precio">0.00</span></td>
        <td>
            <input type="number"
                   name="productos[${indexOC}][cantidad]"
                   class="form-control form-control-sm text-center cantidad"
                   min="1"
                   value="1"
                   onkeydown="return soloFlechasCantidad(event);"
                   oninput="actualizarSubtotal(this)">
        </td>
        <td class="text-end"><strong class="subtotal">0.00</strong></td>
        <td class="text-center">
            <button type="button"
                    class="btn btn-danger btn-sm"
                    onclick="eliminarProducto(this)">
                Quitar
            </button>
        </td>
    `;
            tbody.appendChild(tr);
            indexOC++;
        }

        function eliminarProducto(btn) {
            btn.closest('.producto-item').remove();
            renumerar();
            actualizarTotales();
        }

        function renumerar() {
            const filas = document.querySelectorAll('.producto-item');
            filas.forEach((fila, i) => {
                fila.querySelector('.select-producto').name = `productos[${i}][id_producto]`;
                fila.querySelector('.cantidad').name = `productos[${i}][cantidad]`;
            });
            indexOC = filas.length;
        }

        function actualizarPrecio(select) {
            const fila = select.closest('.producto-item');
            const precioSpan = fila.querySelector('.precio');
            const option = select.selectedOptions[0];
            const precio = parseFloat(option?.dataset?.precio || 0);

            precioSpan.textContent = precio.toFixed(2);

            // Actualizar subtotal de esta fila
            const inputCantidad = fila.querySelector('.cantidad');
            actualizarSubtotal(inputCantidad);
        }

        function actualizarSubtotal(input) {
            const fila = input.closest('.producto-item');
            const precioText = fila.querySelector('.precio').textContent;
            const precio = parseFloat(precioText) || 0;
            const cantidad = parseInt(input.value) || 0;

            const subtotal = precio * cantidad;
            fila.querySelector('.subtotal').textContent = subtotal.toFixed(2);

            actualizarTotales();
        }

        function actualizarTotales() {
            let subtotalGeneral = 0;

            document.querySelectorAll('.subtotal').forEach(el => {
                subtotalGeneral += parseFloat(el.textContent) || 0;
            });

            const ivaGeneral = subtotalGeneral * (IVA_PORCENTAJE / 100);
            const totalGeneral = subtotalGeneral + ivaGeneral;

            document.getElementById('subtotal-general').textContent = subtotalGeneral.toFixed(2);
            document.getElementById('iva-general').textContent = ivaGeneral.toFixed(2);
            document.getElementById('total-general').textContent = totalGeneral.toFixed(2);
        }

        function soloFlechasCantidad(e) {
            // Permitir flechas, delete, backspace, tab, enter
            if ([38, 40, 8, 46, 9, 13].includes(e.keyCode)) return true;
            // Permitir números
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) return true;
            // Bloquear todo lo demás (incluido punto decimal)
            return false;
        }
    </script>
