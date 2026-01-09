@extends('layouts.app')

@section('titulo', 'Factura')

@section('content')

    <h1 class="mb-3">
        {{ $factura->esEditable()
            ? 'Modificar Factura ' . $factura->id_factura
            : 'Ver Factura ' . $factura->id_factura
        }}
    </h1>

    {{-- MENSAJE SOLO VISUALIZACIÓN --}}
    @if(!$factura->esEditable())
        <div class="alert bg-concho text-white">
            Esta factura se encuentra
            {{ $factura->estado_fac === 'APR' ? 'aprobada' : 'anulada' }}.
            Solo está disponible para visualización.
        </div>
    @endif

    {{-- ABRIR FORM SOLO SI ESTÁ ABIERTA --}}
    @if($factura->esEditable())
        <form action="{{ route('facturas.update', $factura->id_factura) }}" method="POST" id="formEditarFactura">
            @csrf
            @method('PUT')
            @endif

            {{-- CLIENTE / DESCRIPCIÓN --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $factura->cliente->cli_nombre }}"
                           disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Descripción</label>
                    @if($factura->esEditable())
                        <input type="text"
                               name="fac_descripcion"
                               class="form-control"
                               value="{{ $factura->fac_descripcion }}">
                    @else
                        <p class="form-control-plaintext">
                            {{ $factura->fac_descripcion ?: '(Sin descripción)' }}
                        </p>
                    @endif
                </div>
            </div>

            <h4 class="mb-2">Productos</h4>

            {{-- TABLA DE PRODUCTOS --}}
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-custom">
                    <thead>
                    <tr>
                        <th style="width: 40%;">Producto</th>
                        <th class="text-end" style="width: 15%;">Precio Unit.</th>
                        <th class="text-center" style="width: 15%;">Cantidad</th>
                        <th class="text-end" style="width: 15%;">Subtotal</th>
                        @if($factura->esEditable())
                            <th class="text-center" style="width: 15%;">Acción</th>
                        @endif
                    </tr>
                    </thead>

                    <tbody id="contenedor-productos">
                    @foreach ($factura->detalles as $i => $detalle)
                        <tr class="producto-item">
                            <td>
                                @if($factura->esEditable())
                                    <select name="productos[{{ $i }}][id_producto]"
                                            class="form-select form-select-sm"
                                            onchange="actualizarPrecio(this)">
                                        <option value="">Seleccione un producto</option>
                                        @foreach($productos as $producto)
                                            <option value="{{ $producto->id_producto }}"
                                                    data-precio="{{ $producto->pro_precio_venta }}"
                                                {{ $detalle->id_producto === $producto->id_producto ? 'selected' : '' }}>
                                                {{ $producto->pro_descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{ $detalle->producto->pro_descripcion }}
                                @endif
                            </td>

                            <td class="text-end align-middle">
                                <span class="precio">{{ number_format($detalle->pxf_precio, 2) }}</span>
                            </td>

                            <td class="text-center">
                                @if($factura->esEditable())
                                    <input type="number"
                                           name="productos[{{ $i }}][cantidad]"
                                           class="form-control form-control-sm text-center cantidad"
                                           min="1"
                                           value="{{ $detalle->pxf_cantidad }}"
                                           oninput="actualizarSubtotal(this)">
                                @else
                                    {{ $detalle->pxf_cantidad }}
                                @endif
                            </td>

                            <td class="text-end align-middle">
                                <strong class="subtotal">{{ number_format($detalle->pxf_subtotal, 2) }}</strong>
                            </td>

                            @if($factura->esEditable())
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-danger btn-sm"
                                            onclick="eliminarProducto(this)">
                                        <small>Quitar</small>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- AGREGAR PRODUCTO --}}
            @if($factura->esEditable())
                <div class="mb-3">
                    <button type="button"
                            class="btn btn-concho btn-sm"
                            onclick="agregarProducto()">
                        + Agregar producto
                    </button>
                </div>
            @endif

            <div class="d-flex justify-content-center mt-2" id="paginacion-productos"></div>


            {{-- RESUMEN --}}
            <div class="row align-items-end">
                <div class="col-md-4">
                    <h5 class="mb-2">RESUMEN</h5>

                    <table class="table table-bordered mb-3">
                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-end" id="subtotal-general">{{ number_format($resumen['subtotal'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>IVA:</th>
                            <td class="text-end" id="iva-general">{{ number_format($resumen['iva'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>TOTAL:</th>
                            <td class="text-end">
                                <strong id="total-general">{{ number_format($resumen['total'], 2) }}</strong>
                            </td>
                        </tr>
                    </table>

                    <div class="d-flex gap-3 mt-3">
                        <a href="{{ route('facturas.index') }}"
                           class="btn btn-concho px-5 text-nowrap">
                            {{ $factura->esEditable() ? 'Cancelar' : 'Volver' }}
                        </a>

                        @if($factura->esEditable())
                            <button type="button"
                                    class="btn btn-concho px-5 text-nowrap"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalGuardarFactura">
                                Guardar Cambios
                            </button>

                            <button type="button"
                                    class="btn btn-concho px-5 text-nowrap"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAprobarFactura">
                                Aprobar Factura
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if($factura->esEditable())
        </form>
    @endif


    {{-- MODAL CONFIRMAR GUARDADO --}}
    @if($factura->esEditable())
        <div class="modal fade" id="modalGuardarFactura" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar guardado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        ¿Está seguro de guardar los cambios?
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="button"
                                class="btn btn-primary"
                                onclick="document.getElementById('formEditarFactura').submit()">
                            Sí, guardar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CONFIRMAR APROBACIÓN --}}
    @if($factura->esEditable())
        <div class="modal fade" id="modalAprobarFactura" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar aprobación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        ¿Está seguro de aprobar esta factura? Una vez aprobada no podrá modificarse.
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <form id="formAprobar"
                              action="{{ route('facturas.aprobar', $factura->id_factura) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                Sí, aprobar
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- SCRIPT SOLO SI ABI (NO CARGAR EN MODO VER) --}}
    @if($factura->esEditable())
        <script>
            const CONFIG = @json($config);

            let indexProducto = {{ $factura->detalles->count() }};
            const msgSeleccioneProducto = "Seleccione un producto";
            const msgQuitar = "Quitar";

            const PAGE_SIZE = 10;
            let currentPage = 1;

            function renderPaginacion() {
                const filas = document.querySelectorAll('.producto-item');
                const totalPaginas = Math.ceil(filas.length / PAGE_SIZE) || 1;

                if (currentPage > totalPaginas) {
                    currentPage = totalPaginas;
                }

                filas.forEach((fila, index) => {
                    const inicio = (currentPage - 1) * PAGE_SIZE;
                    const fin = currentPage * PAGE_SIZE;

                    fila.style.display =
                        (index >= inicio && index < fin) ? '' : 'none';
                });

                dibujarControles(totalPaginas);
            }

            function dibujarControles(totalPaginas) {
                const contenedor = document.getElementById('paginacion-productos');
                contenedor.innerHTML = '';

                if (totalPaginas <= 1) return;

                const nav = document.createElement('ul');
                nav.className = 'pagination pagination-sm';

                // Botón Anterior
                nav.appendChild(
                    crearBoton('«', currentPage > 1, () => {
                        currentPage--;
                        renderPaginacion();
                    })
                );

                // Números de página
                for (let i = 1; i <= totalPaginas; i++) {
                    nav.appendChild(
                        crearBoton(i, true, () => {
                            currentPage = i;
                            renderPaginacion();
                        }, i === currentPage)
                    );
                }

                // Botón Siguiente
                nav.appendChild(
                    crearBoton('»', currentPage < totalPaginas, () => {
                        currentPage++;
                        renderPaginacion();
                    })
                );

                contenedor.appendChild(nav);
            }

            function crearBoton(texto, habilitado, accion, activo = false) {
                const li = document.createElement('li');
                li.className = 'page-item';

                if (!habilitado) li.classList.add('disabled');
                if (activo) li.classList.add('active');

                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = texto;

                a.onclick = (e) => {
                    e.preventDefault();
                    if (habilitado) accion();
                };

                li.appendChild(a);
                return li;
            }


            function agregarProducto() {
                const tbody = document.getElementById('contenedor-productos');
                const tr = document.createElement('tr');
                currentPage = Math.ceil(
                    document.querySelectorAll('.producto-item').length / PAGE_SIZE
                );
                renderPaginacion();
                tr.classList.add('producto-item');

                tr.innerHTML = `
                    <td>
                        <select name="productos[${indexProducto}][id_producto]"
                                class="form-select form-select-sm"
                                onchange="actualizarPrecio(this)">
                            <option value="">${msgSeleccioneProducto}</option>
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
                            <small>${msgQuitar}</small>
                        </button>
                    </td>
                `;

                tbody.appendChild(tr);
                indexProducto++;
            }

            function eliminarProducto(btn) {
                btn.closest('.producto-item').remove();
                actualizarTotales();
                renderPaginacion();
            }


            function actualizarPrecio(select) {
                const fila = select.closest('.producto-item');
                const precio = select.options[select.selectedIndex].dataset.precio || 0;

                fila.querySelector('.precio').textContent = parseFloat(precio).toFixed(2);

                const cantidadInput = fila.querySelector('.cantidad');
                if (cantidadInput.value) {
                    actualizarSubtotal(cantidadInput);
                } else {
                    actualizarTotales();
                }
            }

            function actualizarSubtotal(inputCantidad) {
                const fila = inputCantidad.closest('.producto-item');
                const precioText = fila.querySelector('.precio').textContent;
                const precio = parseFloat(precioText) || 0;
                const cantidad = parseFloat(inputCantidad.value) || 0;

                fila.querySelector('.subtotal').textContent = (precio * cantidad).toFixed(2);
                actualizarTotales();
            }

            function actualizarTotales() {
                let subtotalGeneral = 0;

                document.querySelectorAll('.producto-item').forEach(fila => {
                    const subtotal = parseFloat(fila.querySelector('.subtotal').textContent) || 0;
                    subtotalGeneral += subtotal;
                });


                const iva = subtotalGeneral * CONFIG.iva_porcentaje;
                const total = subtotalGeneral + iva;

                document.getElementById('subtotal-general').textContent = subtotalGeneral.toFixed(2);
                document.getElementById('iva-general').textContent = iva.toFixed(2);
                document.getElementById('total-general').textContent = total.toFixed(2);
            }

            document.addEventListener('DOMContentLoaded', () => {
                renderPaginacion();
            });

        </script>
    @endif

@endsection
