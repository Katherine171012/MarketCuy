@extends('layouts.app')

@section('titulo', 'Generar Orden de Compra')

@section('contenido')

    <h1 class="mb-3">Generar Orden de Compra</h1>

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

    <form action="{{ route('compras.store') }}" method="POST" id="formCrearOC">
        @csrf

        {{-- CABECERA (solo lectura) --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3">DATOS DE LA ORDEN</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ID Orden</label>
                        <input type="text" class="form-control" value="{{ $idCompra ?? 'OC-XXXXX' }}" disabled>
                        <small class="text-muted">Se genera automáticamente.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha/Hora</label>
                        <input type="text" class="form-control" value="{{ now() }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control" value="ABI" disabled>
                        <small class="text-muted">La orden se crea en estado Abierto.</small>
                    </div>
                </div>

                {{-- PROVEEDOR con búsqueda incremental --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">
                            Proveedor <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control form-control-sm mb-2"
                               id="buscarProveedor"
                               placeholder="Escribe para filtrar proveedor...">

                        <select name="id_proveedor"
                                id="selectProveedor"
                                class="form-select @error('id_proveedor') is-invalid @enderror">
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $prv)
                                <option value="{{ trim($prv->id_proveedor) }}">
                                    {{ trim($prv->prv_nombre) }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_proveedor')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted">
                            Se muestra el nombre; internamente se guarda el ID.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-2">Detalle de Productos</h4>

        {{-- TABLA DETALLE --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-custom">
                <thead>
                <tr>
                    <th style="width:42%">Producto</th>
                    <th class="text-end" style="width:16%">Valor Unit.</th>
                    <th class="text-center" style="width:14%">Cantidad</th>
                    <th class="text-end" style="width:16%">Subtotal</th>
                    <th class="text-center" style="width:12%">Acción</th>
                </tr>
                </thead>

                <tbody id="contenedor-detalle">
                {{-- FILA BASE --}}
                <tr class="item-detalle">
                    <td>
                        {{-- Buscador incremental por fila --}}
                        <input type="text"
                               class="form-control form-control-sm mb-2 buscador-producto"
                               placeholder="Escribe para filtrar producto..."
                               oninput="filtrarProductosFila(this)">

                        <select name="productos[0][id_producto]"
                                class="form-select form-select-sm producto-select"
                                onchange="actualizarPrecioOC(this)">
                            <option value="">Seleccione un producto</option>
                            @foreach($productos as $p)
                                <option value="{{ trim($p->id_producto) }}"
                                        data-precio="{{ (float)($p->pro_valor_compra ?? 0) }}">
                                    {{ trim($p->pro_descripcion) }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td class="text-end align-middle">
                        <span class="precio">0.00</span>
                    </td>

                    <td>
                        <input type="number"
                               name="productos[0][cantidad]"
                               class="form-control form-control-sm text-center cantidad"
                               min="1"
                               step="1"
                               value="1"
                               disabled
                               oninput="actualizarSubtotalOC(this)"
                               onkeydown="return false;"
                               onpaste="return false;">
                    </td>

                    <td class="text-end align-middle">
                        <strong class="subtotal">0.00</strong>
                    </td>

                    <td class="text-center">
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="eliminarItemOC(this)">
                            <small>Quitar</small>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        {{-- AGREGAR PRODUCTO --}}
        <div class="mb-3">
            <button type="button"
                    class="btn btn-concho btn-sm"
                    onclick="agregarItemOC()">
                + Agregar producto
            </button>
        </div>

        {{-- RESUMEN --}}
        <div class="row align-items-end">
            <div class="col-md-5">
                <h5 class="mb-2">RESUMEN</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>Subtotal:</th>
                        <td class="text-end" id="subtotal-general">0.00</td>
                    </tr>
                    <tr>
                        <th>IVA (15%):</th>
                        <td class="text-end" id="iva-general">0.00</td>
                    </tr>
                    <tr>
                        <th>TOTAL:</th>
                        <td class="text-end">
                            <strong id="total-general">0.00</strong>
                        </td>
                    </tr>
                </table>

                {{-- BOTONES --}}
                <div class="d-flex gap-3">
                    <a href="{{ route('compras.index') }}"
                       class="btn btn-concho px-5 text-nowrap">
                        Cancelar
                    </a>

                    <button type="button"
                            class="btn btn-concho px-5 text-nowrap"
                            data-bs-toggle="modal"
                            data-bs-target="#modalGuardarOC">
                        Guardar
                    </button>

                    <button type="button"
                            class="btn btn-concho px-5 text-nowrap"
                            data-bs-toggle="modal"
                            data-bs-target="#modalAprobarOC">
                        Guardar y aprobar
                    </button>
                </div>

                <small class="text-muted d-block mt-2">
                    Nota: el sistema guardará la OC en estado ABI. Si eliges “Guardar y aprobar”, se intentará aprobar inmediatamente.
                </small>
            </div>
        </div>

        {{-- HIDDEN para indicar acción --}}
        <input type="hidden" name="accion" id="accionOC" value="guardar">
    </form>

    {{-- MODAL GUARDAR --}}
    <div class="modal fade" id="modalGuardarOC" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar guardado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de guardar esta orden de compra?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-concho" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-concho" onclick="enviarOC('guardar')">
                        Sí, guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL APROBAR --}}
    <div class="modal fade" id="modalAprobarOC" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar aprobación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de guardar y aprobar esta orden de compra?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-concho" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-concho" onclick="enviarOC('aprobar')">
                        Sí, guardar y aprobar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const IVA_PORC = 15;
        let indexItemOC = 1;

        // -------- Proveedor: filtro incremental --------
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('buscarProveedor');
            const select = document.getElementById('selectProveedor');
            if (!input || !select) return;

            const opcionesOriginales = Array.from(select.options).map(o => ({
                value: o.value,
                text: o.textContent
            }));

            input.addEventListener('input', () => {
                const q = (input.value || '').toLowerCase().trim();

                select.innerHTML = '';
                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = 'Seleccione un proveedor';
                select.appendChild(opt0);

                opcionesOriginales.forEach(o => {
                    if (!o.value) return;
                    if (q === '' || o.text.toLowerCase().includes(q)) {
                        const opt = document.createElement('option');
                        opt.value = o.value;
                        opt.textContent = o.text;
                        select.appendChild(opt);
                    }
                });
            });
        });

        // -------- Productos: filtro incremental por fila --------
        function filtrarProductosFila(input) {
            const fila = input.closest('.item-detalle');
            if (!fila) return;

            const select = fila.querySelector('.producto-select');
            if (!select) return;

            const q = (input.value || '').toLowerCase().trim();

            Array.from(select.options).forEach((opt, idx) => {
                if (idx === 0) return; // "Seleccione..."
                const txt = (opt.textContent || '').toLowerCase();
                opt.hidden = !(q === '' || txt.includes(q));
            });

            // Si el seleccionado queda oculto, lo reseteamos
            const selOpt = select.options[select.selectedIndex];
            if (selOpt && selOpt.hidden) {
                select.value = '';
                actualizarPrecioOC(select);
            }
        }

        // -------- Agregar / quitar filas --------
        function agregarItemOC() {
            const tbody = document.getElementById('contenedor-detalle');
            const tr = document.createElement('tr');
            tr.classList.add('item-detalle');

            tr.innerHTML = `
                <td>
                    <input type="text"
                           class="form-control form-control-sm mb-2 buscador-producto"
                           placeholder="Escribe para filtrar producto..."
                           oninput="filtrarProductosFila(this)">

                    <select name="productos[${indexItemOC}][id_producto]"
                            class="form-select form-select-sm producto-select"
                            onchange="actualizarPrecioOC(this)">
                        <option value="">Seleccione un producto</option>
                        @foreach($productos as $p)
            <option value="{{ trim($p->id_producto) }}"
                                    data-precio="{{ (float)($p->pro_valor_compra ?? 0) }}">
                                {{ trim($p->pro_descripcion) }}
            </option>
@endforeach
            </select>
        </td>

        <td class="text-end align-middle">
            <span class="precio">0.00</span>
        </td>

        <td>
            <input type="number"
                   name="productos[${indexItemOC}][cantidad]"
                           class="form-control form-control-sm text-center cantidad"
                           min="1"
                           step="1"
                           value="1"
                           disabled
                           oninput="actualizarSubtotalOC(this)"
                           onkeydown="return false;"
                           onpaste="return false;">
                </td>

                <td class="text-end align-middle">
                    <strong class="subtotal">0.00</strong>
                </td>

                <td class="text-center">
                    <button type="button"
                            class="btn btn-danger btn-sm"
                            onclick="eliminarItemOC(this)">
                        <small>Quitar</small>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            indexItemOC++;
            actualizarTotalesOC();
        }

        function eliminarItemOC(btn) {
            const fila = btn.closest('.item-detalle');
            if (fila) fila.remove();
            actualizarTotalesOC();
            renumerarIndices();
        }

        // Importante: renumerar índices para que el backend reciba arreglo limpio
        function renumerarIndices() {
            const filas = document.querySelectorAll('#contenedor-detalle .item-detalle');
            filas.forEach((fila, idx) => {
                const select = fila.querySelector('select.producto-select');
                const inputCant = fila.querySelector('input.cantidad');

                if (select) select.name = `productos[${idx}][id_producto]`;
                if (inputCant) inputCant.name = `productos[${idx}][cantidad]`;
            });

            indexItemOC = filas.length;
        }

        // -------- Precio / subtotales --------
        function actualizarPrecioOC(select) {
            const fila = select.closest('.item-detalle');
            const precio = select.options[select.selectedIndex]?.dataset?.precio || 0;

            const cantidad = fila.querySelector('.cantidad');
            fila.querySelector('.precio').textContent = parseFloat(precio).toFixed(2);

            if (select.value) {
                cantidad.disabled = false;
                if (!cantidad.value || parseInt(cantidad.value, 10) < 1) cantidad.value = 1;
            } else {
                cantidad.disabled = true;
                cantidad.value = 1;
            }

            actualizarSubtotalOC(cantidad);
        }

        function actualizarSubtotalOC(input) {
            const fila = input.closest('.item-detalle');
            const precio = parseFloat(fila.querySelector('.precio').textContent) || 0;
            const cant = parseInt(input.value, 10) || 0;

            if (cant < 1 && !input.disabled) input.value = 1;

            const sub = (precio * (parseInt(input.value, 10) || 0));
            fila.querySelector('.subtotal').textContent = sub.toFixed(2);

            actualizarTotalesOC();
        }

        function actualizarTotalesOC() {
            let subtotal = 0;

            document.querySelectorAll('.item-detalle .subtotal').forEach(el => {
                subtotal += parseFloat(el.textContent) || 0;
            });

            const iva = subtotal * (IVA_PORC / 100);
            const total = subtotal + iva;

            document.getElementById('subtotal-general').textContent = subtotal.toFixed(2);
            document.getElementById('iva-general').textContent = iva.toFixed(2);
            document.getElementById('total-general').textContent = total.toFixed(2);
        }

        // -------- Enviar --------
        function enviarOC(accion) {
            document.getElementById('accionOC').value = accion;
            document.getElementById('formCrearOC').submit();
        }
    </script>

@endsection
