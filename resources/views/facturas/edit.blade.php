@extends('layouts.app')

@section('titulo', 'Factura')

@section('content')

    <h1 class="mb-3">
        {{ $factura->esEditable()
        ? 'Modificar Factura ' . $factura->id_factura
        : 'Ver Factura ' . $factura->id_factura
            }}
    </h1>
    <input type="hidden" id="id_factura_hidden" value="{{ $factura->id_factura }}">

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

        {{-- CABECERA DE FACTURA --}}
        <div class="row mb-3">

            {{-- ID FACTURA --}}
            <div class="col-md-3">
                <label class="form-label">N° Factura</label>
                <input type="text" class="form-control" value="{{ $factura->id_factura }}" readonly>
            </div>

            {{-- FECHA --}}
            <div class="col-md-3">
                <label class="form-label">Fecha</label>
                <input type="text" class="form-control"
                    value="{{ \Carbon\Carbon::parse($factura->fac_fecha_hora)->format('d/m/Y H:i') }}" readonly>
            </div>

            {{-- CLIENTE --}}
            <div class="col-md-6">
                <label class="form-label">Cliente</label>
                <input type="text" class="form-control" value="{{ $factura->cliente->cli_nombre }}" readonly>
            </div>

        </div>

        <div class="row mb-3">

            {{-- CÉDULA / RUC --}}
            <div class="col-md-3">
                <label class="form-label">Cédula / RUC</label>
                <input type="text" class="form-control" value="{{ $factura->cliente->cli_ruc_ced }}" readonly>
            </div>

            {{-- DESCRIPCIÓN --}}
            <div class="col-md-9">
                <label class="form-label">Descripción</label>

                @if($factura->esEditable())
                    <input type="text" name="fac_descripcion" class="form-control" value="{{ $factura->fac_descripcion }}">
                @else
                    <input type="text" class="form-control" value="{{ $factura->fac_descripcion ?: '(Sin descripción)' }}"
                        readonly>
                @endif
            </div>
        </div>

        {{-- 1. CIERRE CORRECTO DEL FORM DE CABECERA --}}
        @if($factura->esEditable())
            </form>
        @endif

    <h4 class="mb-2">Productos</h4>

    <div class="table-responsive mb-3">
        <table class="table table-bordered table-hover table-custom align-middle">
            <thead>
                <tr>
                    {{-- Todos los encabezados con el mismo estilo --}}
                    <th class="text-center" style="width: 8%">ID</th>
                    <th style="width: 32%">Producto</th>
                    <th class="text-center" style="width: 10%">Unidad</th>
                    <th class="text-center" style="width: 10%">Cantidad</th>
                    <th class="text-end" style="width: 12%">Precio Unit.</th>
                    <th class="text-end" style="width: 13%">Subtotal</th>
                    <th class="text-center" style="width: 15%">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($factura->detalles as $detalle)
                    <tr>
                        {{-- ID: Centrado y con la misma fuente que el resto --}}
                        <td class="text-center">
                            {{ $detalle->id_producto }}
                        </td>

                        {{-- Producto: Mantiene negrita para jerarquía, pero misma fuente --}}
                        <td class="fw-bold">
                            {{ $detalle->producto->pro_nombre }}
                        </td>

                        <td class="text-center">
                            {{ $detalle->producto->pro_um_venta }}
                        </td>

                        <td class="text-center">
                            {{ $detalle->pxf_cantidad }}
                        </td>

                        <td class="text-end">
                            {{ number_format($detalle->pxf_precio, 2) }}
                        </td>

                        <td class="text-end fw-bold">
                            {{ number_format($detalle->pxf_subtotal, 2) }}
                        </td>

                        <td class="text-center">
                            {{-- Editar --}}
                            @if($factura->esEditable())
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalEditarProducto" data-factura="{{ $factura->id_factura }}"
                                    data-producto="{{ $detalle->id_producto }}" data-cantidad="{{ $detalle->pxf_cantidad }}"
                                    data-stock="{{ $detalle->producto->pro_saldo_final }}">
                                    Editar
                                </button>

                                {{-- Eliminar --}}
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalEliminarProducto" data-factura="{{ $factura->id_factura }}"
                                    data-producto="{{ $detalle->id_producto }}">
                                    Eliminar
                                </button>
                            @endif
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    {{-- AGREGAR PRODUCTO --}}
    @if($factura->esEditable())
        <div class="mb-3">
            <button type="button" class="btn btn-concho btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
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
                <a href="{{ route('facturas.index') }}" class="btn btn-concho text-nowrap">
                    {{ $factura->esEditable() ? 'Cancelar' : 'Volver' }}
                </a>

                @if($factura->esEditable())
                    <button type="button" class="btn btn-concho text-nowrap" data-bs-toggle="modal"
                        data-bs-target="#modalGuardarFactura">
                        Guardar Cambios
                    </button>
                @endif

                {{-- Botón de Aprobar Factura --}}
                @if($factura->esEditable())
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAprobarFactura">
                        Aprobar Factura
                    </button>
                @endif

            </div>
        </div>
    </div>


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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="button" class="btn btn-primary"
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
                    <form id="formAprobarFactura" action="{{ route('facturas.aprobar', $factura->id_factura) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar aprobación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ¿Está seguro de aprobar esta factura? Una vez aprobada no podrá modificarse.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Sí, aprobar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL AGREGAR PRODUCTO --}}
    @if($factura->esEditable())
        <div class="modal fade" id="modalAgregarProducto" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <form method="POST" action="{{ route('facturas.detalle.store', $factura->id_factura) }}">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title">Agregar producto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Producto</label>
                                <select name="id_producto" class="form-select" required>
                                    <option value="">Seleccione un producto</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id_producto }}">
                                            {{ $producto->pro_nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" min="1" required>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <button type="submit" class="btn btn-concho">
                                Agregar
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR PRODUCTO --}}
    @if($factura->esEditable())
        <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" id="formEditarDetalle">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h5 class="modal-title">Editar cantidad de producto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" id="edit-cantidad" name="cantidad" class="form-control" min="1" required>
                                <small class="text-muted">Stock disponible: <span id="stock-disponible"></span></small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ELIMINAR PRODUCTO --}}
    @if($factura->esEditable())
        <div class="modal fade" id="modalEliminarProducto" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" id="formEliminarDetalle">
                        @csrf
                        @method('DELETE')

                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar eliminación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            {{ config('mensajes.M4') }}
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

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

            document.addEventListener('DOMContentLoaded', function () {
                // Inicializar paginación
                renderPaginacion();

                // ========== MODAL DE EDICIÓN ==========
                const modalEditarElement = document.getElementById('modalEditarProducto');
                if (modalEditarElement) {
                    modalEditarElement.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;
                        const facturaId = button.getAttribute('data-factura');
                        const productoId = button.getAttribute('data-producto');
                        const cantidad = button.getAttribute('data-cantidad');
                        const stock = button.getAttribute('data-stock');

                        const form = document.getElementById('formEditarDetalle');
                        form.action = `/facturas/${facturaId}/detalle/${productoId}`;

                        const inputCantidad = document.getElementById('edit-cantidad');
                        const spanStock = document.getElementById('stock-disponible');

                        inputCantidad.value = cantidad;
                        spanStock.textContent = stock;

                        const nuevoInput = inputCantidad.cloneNode(true);
                        inputCantidad.parentNode.replaceChild(nuevoInput, inputCantidad);

                        nuevoInput.addEventListener('input', function () {
                            let cantidadIngresada = parseInt(this.value) || 0;

                            if (cantidadIngresada < 1) {
                                this.value = 1;
                            }

                            if (cantidadIngresada > parseInt(stock)) {
                                // alert('{{ config("mensajes.M36") }}'); // Deshabilitado por UX
                                this.value = stock;
                            }
                        });
                    });
                }

                // ========== MODAL DE ELIMINACIÓN ==========
                const modalEliminarElement = document.getElementById('modalEliminarProducto');
                if (modalEliminarElement) {
                    modalEliminarElement.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;
                        const facturaId = button.getAttribute('data-factura');
                        const productoId = button.getAttribute('data-producto');

                        const form = document.getElementById('formEliminarDetalle');
                        form.action = `/facturas/${facturaId}/detalle/${productoId}`;
                    });
                }
            });
        </script>
    @endif

    @if($factura->esEditable())
        <script>
            const CONFIG = @json($config);
            let indexProducto = {{ $factura->detalles->count() }};
        </script>

        <script src="{{ asset('js/facturas.js') }}"></script>
    @endif
@endsection