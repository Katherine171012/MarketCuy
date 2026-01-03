@extends('layouts.app')

@section('titulo', 'Generar Factura')

@section('content')

    <h1 class="mb-3">Generar Factura</h1>

    {{-- MENSAJES DE ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('facturas.store') }}" method="POST" id="formCrearFactura">
        @csrf

        {{-- CLIENTE / DESCRIPCIÓN --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">
                    Cliente <span class="text-danger">*</span>
                </label>
                <select name="id_cliente" class="form-select">
                    <option value="">Seleccione un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id_cliente }}"
                            {{ old('id_cliente') == $cliente->id_cliente ? 'selected' : '' }}>
                            {{ $cliente->cli_nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Descripción</label>
                <input type="text"
                       name="fac_descripcion"
                       class="form-control"
                       maxlength="30"
                       value="{{ old('fac_descripcion') }}">
            </div>
        </div>

        <h4 class="mb-2">Productos</h4>

        {{-- TABLA DE PRODUCTOS --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-custom">
                <thead>
                <tr>
                    <th style="width:40%">Producto</th>
                    <th class="text-end" style="width:15%">Precio Unit.</th>
                    <th class="text-center" style="width:15%">Cantidad</th>
                    <th class="text-end" style="width:15%">Subtotal</th>
                    <th class="text-center" style="width:15%">Acción</th>
                </tr>
                </thead>
                <tbody id="contenedor-productos">
                {{-- FILA BASE --}}
                <tr class="producto-item">
                    <td>
                        <select name="productos[0][id_producto]"
                                class="form-select form-select-sm"
                                onchange="actualizarPrecio(this)">
                            <option value="">Seleccione un producto</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id_producto }}"
                                        data-precio="{{ $producto->pro_precio_venta }}">
                                    {{ $producto->pro_descripcion }}
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
                </tbody>
            </table>
        </div>

        {{-- AGREGAR PRODUCTO --}}
        <div class="mb-3">
            <button type="button"
                    class="btn btn-concho btn-sm"
                    onclick="agregarProducto()">
                + Agregar producto
            </button>
        </div>

        {{-- RESUMEN --}}
        <div class="row align-items-end">
            <div class="col-md-4">
                <h5 class="mb-2">RESUMEN</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>Subtotal:</th>
                        <td class="text-end" id="subtotal-general">0.00</td>
                    </tr>
                    <tr>
                        <th>IVA (12%):</th>
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
                    <a href="{{ route('facturas.index') }}"
                       class="btn btn-concho px-5 text-nowrap">
                        Cancelar
                    </a>

                    <button type="button"
                            class="btn btn-concho px-5 text-nowrap"
                            data-bs-toggle="modal"
                            data-bs-target="#modalGenerarFactura">
                        Generar Factura
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- MODAL CONFIRMAR --}}
    <div class="modal fade" id="modalGenerarFactura" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar generación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de generar esta factura?
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-concho"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button"
                            class="btn btn-concho"
                            onclick="document.getElementById('formCrearFactura').submit()">
                        Sí, generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        let indexProducto = 1;

        function agregarProducto() {
            const tbody = document.getElementById('contenedor-productos');
            const tr = document.createElement('tr');
            tr.classList.add('producto-item');

            tr.innerHTML = `
                <td>
                    <select name="productos[${indexProducto}][id_producto]"
                            class="form-select form-select-sm"
                            onchange="actualizarPrecio(this)">
                        <option value="">Seleccione un producto</option>
                        @foreach($productos as $producto)
            <option value="{{ $producto->id_producto }}"
                                    data-precio="{{ $producto->pro_precio_venta }}">
                                {{ $producto->pro_descripcion }}
            </option>
@endforeach
            </select>
        </td>
        <td class="text-end align-middle">
            <span class="precio">0.00</span>
        </td>
        <td>
            <input type="number"
                   name="productos[${indexProducto}][cantidad]"
                           class="form-control form-control-sm text-center cantidad"
                           min="1"
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
        }

        function eliminarProducto(btn) {
            btn.closest('.producto-item').remove();
            actualizarTotales();
        }

        function actualizarPrecio(select) {
            const fila = select.closest('.producto-item');
            const precio = select.options[select.selectedIndex].dataset.precio || 0;

            fila.querySelector('.precio').textContent = parseFloat(precio).toFixed(2);

            const cantidad = fila.querySelector('.cantidad');
            if (cantidad.value) {
                actualizarSubtotal(cantidad);
            }
        }

        function actualizarSubtotal(input) {
            const fila = input.closest('.producto-item');
            const precio = parseFloat(fila.querySelector('.precio').textContent) || 0;
            const cantidad = parseFloat(input.value) || 0;

            fila.querySelector('.subtotal').textContent = (precio * cantidad).toFixed(2);
            actualizarTotales();
        }

        function actualizarTotales() {
            let subtotal = 0;

            document.querySelectorAll('.producto-item .subtotal').forEach(el => {
                subtotal += parseFloat(el.textContent) || 0;
            });

            const iva = subtotal * 0.12;
            const total = subtotal + iva;

            document.getElementById('subtotal-general').textContent = subtotal.toFixed(2);
            document.getElementById('iva-general').textContent = iva.toFixed(2);
            document.getElementById('total-general').textContent = total.toFixed(2);
        }
    </script>

@endsection
