// === MÓDULO FACTURAS - JavaScript Unificado ===

// ==========================================
// VARIABLES GLOBALES
// ==========================================
// Se inicializan aquí, pero el Blade del Edit las sobrescribe con el valor real
let indexProducto = 1;
let ivaPorcentaje = 0.12;
const PAGE_SIZE = 10;
let currentPage = 1;

// ==========================================
// FUNCIONES GENERALES (INDEX / COMÚN)
// ==========================================
// Usamos var para permitir la re-declaración o verificamos si ya existe
if (typeof Facturacion === 'undefined') {
    window.Facturacion = {
        indexProducto: 0,
        // ... el resto de tu lógica aquí
    };
}
function confirmarAnulacion(idFactura) {
    document.getElementById('factura-anular-id').textContent = idFactura;
    document.getElementById('formAnularFactura').action = `/facturas/${idFactura}/anular`;
    new bootstrap.Modal(document.getElementById('modalAnularFactura')).show();
}

// Genera el contenido de los <select> buscando el JSON en el HTML
function generarOpcionesProductos() {
    const dataElement = document.getElementById('lista-productos-json');
    if (!dataElement) return '<option value="">Sin productos disponibles</option>';

    try {
        const productos = JSON.parse(dataElement.dataset.json);
        return productos.map(p => `
            <option value="${p.id_producto}" data-precio="${p.pro_precio_venta}">
                ${p.pro_descripcion}
            </option>
        `).join('');
    } catch (error) {
        console.error("Error en JSON de productos:", error);
        return '<option value="">Error al cargar productos</option>';
    }
}

// ==========================================
// LÓGICA DE PRODUCTOS (CREATE Y EDIT)
// ==========================================

function agregarProducto() {
    const tbody = document.getElementById('contenedor-productos');
    if (!tbody) return;

    // Usamos el contador global sincronizado con el Blade
    const idx = window.indexProducto;

    const tr = document.createElement('tr');
    tr.classList.add('producto-item');

    tr.innerHTML = `
        <td>
            <select name="productos[${idx}][id_producto]" class="form-select form-select-sm" onchange="actualizarPrecio(this)">
                <option value="">Seleccione un producto</option>
                ${generarOpcionesProductos()}
            </select>
        </td>
        <td class="text-end align-middle"><span class="precio">0.00</span></td>
        <td>
            <input type="number" name="productos[${idx}][cantidad]" class="form-control form-control-sm text-center cantidad" min="1" value="1" oninput="actualizarSubtotal(this)">
        </td>
        <td class="text-end align-middle"><strong class="subtotal">0.00</strong></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(this)">
                <small>Quitar</small>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    window.indexProducto++; // Incrementamos para el siguiente
    renderPaginacion(); // Actualizamos la vista
}
function eliminarProducto(btn) {
    btn.closest('.producto-item').remove();
    actualizarTotales();
    renderPaginacion();
}

function actualizarPrecio(select) {
    const fila = select.closest('.producto-item');
    const precio = select.options[select.selectedIndex].dataset.precio || 0;
    const cantidadInput = fila.querySelector('.cantidad');

    fila.querySelector('.precio').textContent = parseFloat(precio).toFixed(2);

    // Si hay un valor en cantidad, recalculamos el subtotal de esa fila
    if (cantidadInput.value) {
        actualizarSubtotal(cantidadInput);
    } else {
        actualizarTotales();
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

    // Usa la variable global que puede ser actualizada por el Blade
    const iva = subtotal * ivaPorcentaje;
    const total = subtotal + iva;

    document.getElementById('subtotal-general').textContent = subtotal.toFixed(2);
    document.getElementById('iva-general').textContent = iva.toFixed(2);
    document.getElementById('total-general').textContent = total.toFixed(2);
}

// ==========================================
// PAGINACIÓN
// ==========================================

function renderPaginacion() {
    const filas = document.querySelectorAll('.producto-item');
    const totalPaginas = Math.ceil(filas.length / PAGE_SIZE) || 1;

    if (currentPage > totalPaginas) currentPage = totalPaginas;

    filas.forEach((fila, index) => {
        const inicio = (currentPage - 1) * PAGE_SIZE;
        const fin = currentPage * PAGE_SIZE;
        fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
    });

    dibujarControles(totalPaginas);
}

function dibujarControles(totalPaginas) {
    const contenedor = document.getElementById('paginacion-productos');
    if (!contenedor) return;
    contenedor.innerHTML = '';

    if (totalPaginas <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm';

    ul.appendChild(crearBoton('«', currentPage > 1, () => { currentPage--; renderPaginacion(); }));

    for (let i = 1; i <= totalPaginas; i++) {
        ul.appendChild(crearBoton(i, true, () => { currentPage = i; renderPaginacion(); }, i === currentPage));
    }

    ul.appendChild(crearBoton('»', currentPage < totalPaginas, () => { currentPage++; renderPaginacion(); }));

    contenedor.appendChild(ul);
}
// ========================================
// FUNCIÓN CREAR BOTÓN (para paginación)
// ========================================
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
// ========================================
// INICIALIZACIÓN - UN SOLO DOMContentLoaded
// ========================================
document.addEventListener('DOMContentLoaded', () => {

    // 1. PAGINACIÓN
    if (document.getElementById('paginacion-productos')) {
        if (typeof renderPaginacion === 'function') {
            renderPaginacion();
        }
    }

    $('#modalEditarProducto').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que abrió el modal
        var facturaId = button.attr('data-factura'); // Obtén id de factura
        var productoId = button.attr('data-producto'); // Obtén id de producto
        var cantidad = button.attr('data-cantidad'); // Obtén cantidad actual
        var stock = button.attr('data-stock'); // Obtén stock disponible

        var form = $('#formEditarDetalle');
        form.attr('action', `/facturas/${facturaId}/detalle/${productoId}`);

        // Cargar la cantidad actual y el stock disponible
        $('#edit-cantidad').val(cantidad);
        $('#stock-disponible').text(stock);

        // Validación para la cantidad
        $('#edit-cantidad').on('input', function() {
            var cantidadIngresada = $(this).val();
            if (cantidadIngresada < 1) {
                $(this).val(1); // Evitar valores negativos o cero
            }
            if (cantidadIngresada > stock) {
                alert("La cantidad supera el stock disponible.");
                $(this).val(stock); // Limitar la cantidad al stock disponible
            }
        });
    });

    $('#modalEliminarProducto').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que abrió el modal
        var facturaId = button.attr('data-factura'); // Obtén id de factura
        var productoId = button.attr('data-producto'); // Obtén id de producto

        var form = $('#formEliminarDetalle');
        // Construcción de la ruta dinámica para eliminar el detalle de la factura
        form.attr('action', `/facturas/${facturaId}/detalle/${productoId}`); // Asegúrate de que la ruta sea la correcta
    });




}); // <--- Este cierra el DOMContentLoaded del inicio
