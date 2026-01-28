<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    /**
     * Menú principal del módulo Facturación
     */
    public function menu()
    {
        return view('facturas.menu');
    }

    /* ======================================================
     * F5.4.1 – CONSULTA GENERAL DE FACTURAS
     * ====================================================== */
    public function index(Request $request)
    {
        $busquedaActiva =
            $request->filled('id_factura') ||
            $request->filled('cliente') ||
            $request->filled('estado_fac') ||
            $request->filled('fecha_desde') ||
            $request->filled('fecha_hasta');

        if ($busquedaActiva) {
            // 🔍 BÚSQUEDA POR PARÁMETROS (DENTRO DEL INDEX)
            $facturas = Factura::buscarPorParametrosIndex(
                $request->all(),
                $request->get('per_page', 10)
            );
        } else {
            // 📄 LISTADO NORMAL
            $facturas = Factura::obtenerListado(
                $request->get('sort'),
                $request->get('dir'),
                $request->get('per_page', 10)
            );
        }

        return view('facturas.index', compact(
            'facturas',
            'busquedaActiva'
        ));
    }

    /* ======================================================
     * F5.1 – MOSTRAR FORMULARIO DE GENERACIÓN
     * ====================================================== */
    public function create()
    {
        $clientes = Cliente::where('estado_cli', 'ACT')->get();
        $productos = Producto::obtenerActivos();

        return view('facturas.create', compact('clientes', 'productos'));
    }

    /* ======================================================
     * F5.1 – GENERAR FACTURA
     * ====================================================== */
    /* ======================================================
     * F5.1 – CREAR FACTURA (SOLO CABECERA)
     * ====================================================== */
    public function store(Request $request)
    {
        // ---------- VALIDACIONES ----------
        if (empty($request->id_cliente)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('warning', config('mensajes.M44'));
        }

        try {

            $factura = Factura::crearCabecera(
                $request->id_cliente,
                $request->fac_descripcion
            );

            return redirect()
                ->route('facturas.edit', $factura->id_factura)
                ->with('ok', config('mensajes.M46'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    /* ======================================================
     * F5.1 – APROBAR FACTURA
     * ====================================================== */
    public function aprobar(string $idFactura)
    {
        try {
            // Llamar al método aprobarFactura en el modelo
            Factura::aprobarFactura($idFactura);

            return redirect()
                ->route('facturas.index')
                ->with('ok', config('mensajes.M70')); // Mensaje de éxito
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage()); // Mostrar error si ocurre
        }
    }

    /* ======================================================
     * F5.2 – MOSTRAR FORMULARIO DE MODIFICACIÓN
     * ====================================================== */
    public function edit(string $idFactura)
    {
        try {
            $factura = Factura::obtenerParaEdicion($idFactura);
            $productos = Producto::obtenerActivos();

            // 🎯 Calcular resumen usando el Model
            $resumen = [
                'subtotal' => $factura->calcularSubtotal(),
                'iva' => $factura->calcularIva(),
                'total' => $factura->calcularTotal(),
            ];

            // 🎯 Config para JavaScript
            $config = [
                'iva_porcentaje' => Factura::IVA_PORCENTAJE,
            ];

            return view('facturas.edit', compact('factura', 'productos', 'resumen', 'config'));

        } catch (\Exception $e) {
            return redirect()
                ->route('facturas.index')
                ->with('error', $e->getMessage());
        }
    }

    /* ======================================================
     * F5.2 – MODIFICAR FACTURA
     * ====================================================== */
    public function update(Request $request, string $idFactura)
    {
        try {
            $factura = Factura::findOrFail($idFactura);

            if ($factura->estado_fac !== 'ABI') {
                return redirect()
                    ->route('facturas.index')
                    ->with('error', config('mensajes.M48'));
            }

            // Solo actualizamos la cabecera (descripción) y recalculamos totales
            Factura::actualizarCabecera(
                $idFactura,
                $request->fac_descripcion
            );

            return redirect()
                ->route('facturas.index')
                ->with('ok', config('mensajes.M2'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /* ======================================================
     * F5.3 – ANULAR FACTURA
     * ====================================================== */
    /**
     * F5.3 – Mostrar confirmación de anulación (M4)
     */
    public function anular(string $idFactura)
    {
        $factura = Factura::findOrFail($idFactura);

        return view('facturas.anular', compact('factura'));
    }

    /**
     * F5.3 – Ejecutar anulación de factura
     */
    public function destroy($idFactura)
    {
        try {
            Factura::anularFactura($idFactura);

            return redirect()
                ->route('facturas.index')
                ->with('ok', config('mensajes.M47'));

        } catch (\Exception $e) {
            return redirect()
                ->route('facturas.index')
                ->with('error', $e->getMessage());
        }
    }

    /* ======================================================
     * F5.4.2 – BÚSQUEDA POR PARÁMETROS
     * ====================================================== */
    /**
     * F5.4.2 – Mostrar formulario de búsqueda por parámetros
     */
    public function buscar()
    {
        return view('facturas.buscar');
    }

    /**
     * F5.4.2 – Ejecutar búsqueda por parámetros
     */
    public function ejecutarBusqueda(Request $request)
    {
        try {
            $facturas = Factura::buscarPorParametros($request->all());
            return view('facturas.buscar', compact('facturas'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('warning', $e->getMessage());
        }
    }
}
