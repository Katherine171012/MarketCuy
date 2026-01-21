@extends('layouts.app')

@section('titulo', 'Generar Orden de Compra')

@section('contenido')

    <h1 class="mb-3">Generar Orden de Compra</h1>

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
                        <input class="form-control" value="{{ $idCompra }}" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha/Hora</label>
                        <input class="form-control" value="{{ now() }}" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <input class="form-control" value="ABI" disabled>
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
                                <option value="{{ trim($prv->id_proveedor) }}">
                                    {{ trim($prv->prv_nombre) }}
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

                <tbody id="contenedor-detalle">
                <tr class="item-detalle">
                    <td>
                        <select class="form-select form-select-sm producto-select"
                                name="productos[0][id_producto]"
                                onchange="actualizarPrecioOC(this)">
                            <option value="">Seleccione un producto</option>
                            @foreach($productos as $p)
                                <option value="{{ trim($p->id_producto) }}"
                                        data-precio="{{ $p->pro_valor_compra ?? 0 }}">
                                    {{ trim($p->pro_nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td class="text-end"><span class="precio">0.00</span></td>

                    <td>
                        <input type="number"
                               name="productos[0][cantidad]"
                               class="form-control form-control-sm text-center cantidad"
                               min="1" value="1" disabled
                               oninput="actualizarSubtotalOC(this)">
                    </td>

                    <td class="text-end"><strong class="subtotal">0.00</strong></td>

                    <td class="text-center">
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="eliminarItemOC(this)">
                            Quitar
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <button type="button" class="btn btn-concho btn-sm mb-3"
                onclick="agregarItemOC()">+ Agregar producto</button>

        <input type="hidden" name="accion" id="accionOC" value="guardar">

        <div class="row">
            <div class="col-md-5">
                <table class="table table-bordered">
                    <tr><th>Subtotal</th><td class="text-end" id="subtotal-general">0.00</td></tr>
                    <tr><th>IVA (15%)</th><td class="text-end" id="iva-general">0.00</td></tr>
                    <tr><th>TOTAL</th><td class="text-end"><strong id="total-general">0.00</strong></td></tr>
                </table>

                <div class="d-flex gap-2">
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="button" class="btn btn-primary" onclick="enviarOC('guardar')">Guardar</button>
                    <button type="button" class="btn btn-success" onclick="enviarOC('aprobar')">Guardar y aprobar</button>
                </div>
            </div>
        </div>

    </form>

    <script>
        const IVA = 15;
        let indexOC = 1;

        function agregarItemOC() {
            const tbody = document.getElementById('contenedor-detalle');
            const tr = document.createElement('tr');
            tr.className = 'item-detalle';

            tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm producto-select"
                    name="productos[${indexOC}][id_producto]"
                    onchange="actualizarPrecioOC(this)">
                <option value="">Seleccione un producto</option>
                @foreach($productos as $p)
            <option value="{{ trim($p->id_producto) }}"
                        data-precio="{{ $p->pro_valor_compra ?? 0 }}">
                    {{ trim($p->pro_nombre) }}
            </option>
@endforeach
            </select>
        </td>
        <td class="text-end"><span class="precio">0.00</span></td>
        <td>
            <input type="number"
                   name="productos[${indexOC}][cantidad]"
                   class="form-control form-control-sm text-center cantidad"
                   min="1" value="1" disabled
                   oninput="actualizarSubtotalOC(this)">
        </td>
        <td class="text-end"><strong class="subtotal">0.00</strong></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm"
                    onclick="eliminarItemOC(this)">Quitar</button>
        </td>
    `;
            tbody.appendChild(tr);
            indexOC++;
        }

        function eliminarItemOC(btn) {
            btn.closest('.item-detalle').remove();
            renumerar();
            actualizarTotalesOC();
        }

        function renumerar() {
            document.querySelectorAll('.item-detalle').forEach((fila, i) => {
                fila.querySelector('.producto-select').name = `productos[${i}][id_producto]`;
                fila.querySelector('.cantidad').name = `productos[${i}][cantidad]`;
            });
            indexOC = document.querySelectorAll('.item-detalle').length;
        }

        function actualizarPrecioOC(select) {
            const fila = select.closest('.item-detalle');
            const precio = parseFloat(select.selectedOptions[0]?.dataset?.precio || 0);
            fila.querySelector('.precio').textContent = precio.toFixed(2);

            const cant = fila.querySelector('.cantidad');
            if (select.value) cant.disabled = false;
            actualizarSubtotalOC(cant);
        }

        function actualizarSubtotalOC(input) {
            const fila = input.closest('.item-detalle');
            const precio = parseFloat(fila.querySelector('.precio').textContent) || 0;
            const cantidad = parseInt(input.value) || 0;
            fila.querySelector('.subtotal').textContent = (precio * cantidad).toFixed(2);
            actualizarTotalesOC();
        }

        function actualizarTotalesOC() {
            let sub = 0;
            document.querySelectorAll('.subtotal').forEach(s => sub += parseFloat(s.textContent) || 0);
            const iva = sub * IVA / 100;
            document.getElementById('subtotal-general').textContent = sub.toFixed(2);
            document.getElementById('iva-general').textContent = iva.toFixed(2);
            document.getElementById('total-general').textContent = (sub + iva).toFixed(2);
        }

        function enviarOC(acc) {
            document.getElementById('accionOC').value = acc;
            document.getElementById('formCrearOC').submit();
        }
    </script>

@endsection
