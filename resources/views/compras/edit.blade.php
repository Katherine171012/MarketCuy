@extends('layouts.app')

@section('titulo', 'Editar Orden de Compra')

@section('contenido')

    <h1 class="mb-3">Editar Orden de Compra</h1>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('compras.update', trim($compra->id_compra)) }}" method="POST" id="formEditarOC">
        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3">DATOS DE LA ORDEN</h6>

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">ID Orden</label>
                        <input type="text" class="form-control" value="{{ trim($compra->id_compra) }}" disabled>
                        <small class="text-muted">No editable.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha/Hora</label>
                        <input type="text" class="form-control" value="{{ $compra->oc_fecha_hora }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control" value="{{ trim($compra->estado_oc) }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Proveedor <span class="text-danger">*</span>
                        </label>
                        <input class="form-control mb-2" type="text" id="buscarProveedor" placeholder="Escriba para filtrar proveedores...">

                        <select name="id_proveedor" id="selectProveedor" class="form-select">
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $prv)
                                @php
                                    $idPrv = trim($prv->id_proveedor);
                                    $sel = old('id_proveedor', trim($compra->id_proveedor)) === $idPrv ? 'selected' : '';
                                @endphp
                                <option value="{{ $idPrv }}" {{ $sel }}>
                                    {{ trim($prv->prv_nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">DETALLE DE PRODUCTOS</h6>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-custom">
                        <thead>
                        <tr>
                            <th style="width:45%">Producto</th>
                            <th class="text-end" style="width:15%">Valor</th>
                            <th class="text-center" style="width:15%">Cantidad</th>
                            <th class="text-end" style="width:15%">Subtotal</th>
                            <th class="text-center" style="width:10%">Acción</th>
                        </tr>
                        </thead>

                        <tbody id="contenedor-productos">
                        @php
                            $detalle = $detalle ?? collect();

                            // Preparar arrays para rehidratar si hubo errores de validación
                            $oldProductos = old('productos');
                            $items = [];

                            if (is_array($oldProductos) && count($oldProductos) > 0) {
                                $items = $oldProductos;
                            } else {
                                $items = [];
                                foreach ($detalle as $d) {
                                    $items[] = [
                                        'id_producto' => trim($d->id_producto),
                                        'cantidad'    => (int) $d->pxo_cantidad,
                                    ];
                                }
                            }
                        @endphp

                        @foreach($items as $i => $item)
                            @php
                                $idSel = trim($item['id_producto'] ?? '');
                                $cantSel = (int) ($item['cantidad'] ?? 1);
                            @endphp
                            <tr class="producto-item">
                                <td>
                                    <input class="form-control form-control-sm mb-2"
                                           type="text"
                                           placeholder="Buscar producto..."
                                           oninput="filtrarProductos(this)">

                                    <select name="productos[{{ $i }}][id_producto]"
                                            class="form-select form-select-sm select-producto"
                                            onchange="actualizarPrecio(this)">
                                        <option value="">Seleccione un producto</option>
                                        @foreach($productos as $p)
                                            @php
                                                $idP = trim($p->id_producto);
                                                $precioCompra = $p->pro_valor_compra ?? 0;
                                            @endphp
                                            <option value="{{ $idP }}"
                                                    data-precio="{{ $precioCompra }}"
                                                {{ $idSel === $idP ? 'selected' : '' }}>
                                                {{ trim($p->pro_descripcion) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="text-end align-middle">
                                    <span class="precio">0.00</span>
                                </td>

                                <td class="text-center">
                                    <input type="number"
                                           name="productos[{{ $i }}][cantidad]"
                                           class="form-control form-control-sm text-center cantidad"
                                           min="1"
                                           step="1"
                                           value="{{ $cantSel }}"
                                           onkeydown="return soloFlechasCantidad(event);"
                                           onpaste="return false;"
                                           ondrop="return false;"
                                           oninput="actualizarSubtotal(this)">
                                </td>

                                <td class="text-end align-middle">
                                    <strong class="subtotal">0.00</strong>
                                </td>

                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-danger btn-sm"
                                            onclick="eliminarProducto(this)">
                                        <small>Quitar</small>
                                    </button>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="mb-3">
                    <button type="button"
                            class="btn btn-concho btn-sm"
                            onclick="agregarProducto()">
                        + Agregar producto
                    </button>
                </div>

                {{-- RESUMEN --}}
                <div class="row align-items-end mb-3">
                    <div class="col-md-5">
                        <h6 class="mb-2">RESUMEN</h6>
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th>Subtotal:</th>
                                <td class="text-end" id="subtotal-general">0.00</td>
                            </tr>
                            <tr>
                                <th>IVA ({{ (int)($compra->oc_iva ?? 15) }}%):</th>
                                <td class="text-end" id="iva-general">0.00</td>
                            </tr>
                            <tr>
                                <th>TOTAL:</th>
                                <td class="text-end"><strong id="total-general">0.00</strong></td>
                            </tr>
                        </table>
                        <small class="text-muted d-block mt-1">
                            Los valores finales también se recalculan al guardar (SP).
                        </small>
                    </div>
                </div>

                <input type="hidden" name="accion" id="accionGuardar" value="guardar">

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn btn-primary"
                            onclick="document.getElementById('accionGuardar').value='guardar'">
                        Guardar cambios
                    </button>

                    <button type="submit"
                            class="btn btn-concho"
                            onclick="document.getElementById('accionGuardar').value='aprobar'">
                        Guardar y aprobar/activar
                    </button>
                </div>

                <div class="mt-3">
                    <small class="text-muted">
                        La orden solo se puede editar si se encuentra en estado <strong>ABI</strong>.
                    </small>
                </div>

            </div>
        </div>

    </form>

    <script>
        const IVA_PORC = {{ (int)($compra->oc_iva ?? 15) }};
        let indexProducto = {{ is_array($items) ? count($items) : 0 }};

        function soloFlechasCantidad(e) {
            // Permite solo flechas arriba/abajo, tab y shift-tab (nada de escribir)
            const permitidas = ['ArrowUp', 'ArrowDown', 'Tab'];
            return permitidas.includes(e.key);
        }

        function agregarProducto() {
            const tbody = document.getElementById('contenedor-productos');
            const tr = document.createElement('tr');
            tr.classList.add('producto-item');

            tr.innerHTML = `
                <td>
                    <input class="form-control form-control-sm mb-2"
                           type="text"
                           placeholder="Buscar producto..."
                           oninput="filtrarProductos(this)">

                    <select name="productos[${indexProducto}][id_producto]"
                            class="form-select form-select-sm select-producto"
                            onchange="actualizarPrecio(this)">
                        <option value="">Seleccione un producto</option>
                        @foreach($productos as $p)
            <option value="{{ trim($p->id_producto) }}"
                                    data-precio="{{ $p->pro_valor_compra ?? 0 }}">
                                {{ trim($p->pro_descripcion) }}
            </option>
@endforeach
            </select>
        </td>

        <td class="text-end align-middle">
            <span class="precio">0.00</span>
        </td>

        <td class="text-center">
            <input type="number"
                   name="productos[${indexProducto}][cantidad]"
                           class="form-control form-control-sm text-center cantidad"
                           min="1"
                           step="1"
                           value="1"
                           onkeydown="return soloFlechasCantidad(event);"
                           onpaste="return false;"
                           ondrop="return false;"
                           oninput="actualizarSubtotal(this)">
                </td>

                <td class="text-end align-middle">
                    <strong class="subtotal">0.00</strong>
                </td>

                <td class="text-center">
                    <button type="button"
                            class="btn btn-danger btn-sm"
                            onclick="eliminarProducto(this)">
                        <small>Quitar</small>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            indexProducto++;

            recalcularFila(tr);
            actualizarTotales();
        }

        function eliminarProducto(btn) {
            btn.closest('.producto-item').remove();
            actualizarTotales();
        }

        function actualizarPrecio(select) {
            const fila = select.closest('.producto-item');
            const precio = select.options[select.selectedIndex]?.dataset?.precio || 0;

            fila.querySelector('.precio').textContent = parseFloat(precio).toFixed(2);

            const cantidadInput = fila.querySelector('.cantidad');
            if (!cantidadInput.value || parseInt(cantidadInput.value) < 1) {
                cantidadInput.value = 1;
            }

            actualizarSubtotal(cantidadInput);
        }

        function actualizarSubtotal(input) {
            const fila = input.closest('.producto-item');
            const precio = parseFloat(fila.querySelector('.precio').textContent) || 0;
            const cantidad = parseInt(input.value) || 0;

            fila.querySelector('.subtotal').textContent = (precio * cantidad).toFixed(2);
            actualizarTotales();
        }

        function actualizarTotales() {
            let subtotal = 0;

            document.querySelectorAll('.producto-item .subtotal').forEach(el => {
                subtotal += parseFloat(el.textContent) || 0;
            });

            const iva = subtotal * (IVA_PORC / 100);
            const total = subtotal + iva;

            const elSub = document.getElementById('subtotal-general');
            const elIva = document.getElementById('iva-general');
            const elTot = document.getElementById('total-general');

            if (elSub) elSub.textContent = subtotal.toFixed(2);
            if (elIva) elIva.textContent = iva.toFixed(2);
            if (elTot) elTot.textContent = total.toFixed(2);
        }

        function recalcularFila(tr) {
            const select = tr.querySelector('select.select-producto');
            if (!select) return;

            const precio = select.options[select.selectedIndex]?.dataset?.precio || 0;
            tr.querySelector('.precio').textContent = parseFloat(precio).toFixed(2);

            const cantidadInput = tr.querySelector('.cantidad');
            actualizarSubtotal(cantidadInput);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.producto-item').forEach(tr => recalcularFila(tr));
            actualizarTotales();
        });

        // Filtro de proveedores (búsqueda incremental)
        document.getElementById('buscarProveedor')?.addEventListener('input', function () {
            const txt = this.value.toLowerCase();
            const sel = document.getElementById('selectProveedor');
            if (!sel) return;

            Array.from(sel.options).forEach(opt => {
                if (!opt.value) return;
                opt.hidden = !opt.text.toLowerCase().includes(txt);
            });
        });

        // Filtro incremental por fila (productos)
        function filtrarProductos(input) {
            const txt = input.value.toLowerCase();
            const fila = input.closest('.producto-item');
            const sel = fila.querySelector('select.select-producto');
            if (!sel) return;

            Array.from(sel.options).forEach(opt => {
                if (!opt.value) return;
                opt.hidden = !opt.text.toLowerCase().includes(txt);
            });
        }
    </script>

@endsection
