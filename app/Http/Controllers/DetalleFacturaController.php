<?php

namespace App\Http\Controllers;

use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetalleFacturaController extends Controller
{
    /**
     * Agregar producto a factura (INSERT)
     */
    public function store(Request $request, string $idFactura)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $factura = Factura::where('id_factura', $idFactura)->firstOrFail();

            // Verificar si es editable
            if ($factura->estado_fac !== 'ABI') {
                return back()->with('codigo_mensaje', 'M48')
                    ->with('tipo_mensaje', 'warning');
            }

            $producto = Producto::where('id_producto', $request->id_producto)
                ->where('estado_prod', 'ACT')
                ->firstOrFail();

            // Verificar si ya existe en la factura
            $existe = DB::table('proxfac')
                ->where('id_factura', $idFactura)
                ->where('id_producto', $request->id_producto)
                ->exists();

            if ($existe) {
                return back()->with('codigo_mensaje', 'M67')
                    ->with('tipo_mensaje', 'warning');
            }

            // Validar Stock
            if ($request->cantidad > $producto->pro_saldo_final) {
                return back()->with('codigo_mensaje', 'M36')
                    ->with('tipo_mensaje', 'warning');
            }

            // Calcular subtotal
            $subtotal = (int) $request->cantidad * (float) $producto->pro_precio_venta;

            // Insertar directamente en la BD
            DB::table('proxfac')->insert([
                'id_factura' => $idFactura,
                'id_producto' => $producto->id_producto,
                'pxf_cantidad' => (int) $request->cantidad,
                'pxf_precio' => (float) $producto->pro_precio_venta,
                'pxf_subtotal' => $subtotal,
                'estado_pxf' => 'ACT',
                'sync_updated_at' => now(),
            ]);

            $this->recalcularTotal($idFactura);

            DB::commit();

            return redirect()->route('facturas.edit', $idFactura)
                ->with('codigo_mensaje', 'M50')
                ->with('tipo_mensaje', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('codigo_mensaje', 'gen.error')
                ->with('tipo_mensaje', 'danger');
        }
    }

    /**
     * Editar cantidad del producto (UPDATE)
     */
    public function update(Request $request, string $idFactura, string $idProducto)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $factura = Factura::where('id_factura', $idFactura)->firstOrFail();

            if ($factura->estado_fac !== 'ABI') {
                return back()->with('codigo_mensaje', 'M48')
                    ->with('tipo_mensaje', 'warning');
            }

            $detalle = DB::table('proxfac')
                ->where('id_factura', trim($idFactura))
                ->where(DB::raw('TRIM(id_producto)'), trim($idProducto))
                ->first();

            if (!$detalle) {
                return back()->with('codigo_mensaje', 'gen.error')
                    ->with('tipo_mensaje', 'danger');
            }

            $producto = Producto::where('id_producto', $idProducto)->firstOrFail();

            // Validar stock
            if ((int) $request->cantidad > $producto->pro_saldo_final) {
                DB::rollBack();
                return back()->with('codigo_mensaje', 'M36')
                    ->with('tipo_mensaje', 'warning');
            }

            // Calcular nuevo subtotal
            $nuevoSubtotal = (int) $request->cantidad * (float) $detalle->pxf_precio;

            // Actualización en la BD
            $actualizado = DB::table('proxfac')
                ->where('id_factura', trim($idFactura))
                ->where(DB::raw('TRIM(id_producto)'), trim($idProducto)) // FIX: Handle whitespace in DB
                ->update([
                    'pxf_cantidad' => (int) $request->cantidad,
                    'pxf_subtotal' => $nuevoSubtotal,
                    'sync_updated_at' => now(),
                ]);

            if (!$actualizado) {
                DB::rollBack();
                return back()->with('codigo_mensaje', 'gen.error')
                    ->with('tipo_mensaje', 'danger');
            }

            $this->recalcularTotal($idFactura);

            DB::commit();

            return redirect()->route('facturas.edit', $idFactura)
                ->with('codigo_mensaje', 'M2')
                ->with('tipo_mensaje', 'success');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('codigo_mensaje', 'gen.error')
                ->with('tipo_mensaje', 'danger')
                ->with('error', $e->getMessage()); // DEBUG: Show actual error
        }
    }

    /**
     * Eliminar producto de factura (DELETE)
     */
    public function destroy(string $idFactura, string $idProducto)
    {
        try {
            DB::beginTransaction();

            $factura = Factura::where('id_factura', $idFactura)->firstOrFail();

            if ($factura->estado_fac !== 'ABI') {
                return back()->with('codigo_mensaje', 'M48')
                    ->with('tipo_mensaje', 'warning');
            }

            // Eliminar registro de la BD
            $eliminado = DB::table('proxfac')
                ->where('id_factura', trim($idFactura))
                ->where(DB::raw('TRIM(id_producto)'), trim($idProducto))
                ->delete();

            if (!$eliminado) {
                DB::rollBack();
                return back()->with('codigo_mensaje', 'gen.error')
                    ->with('tipo_mensaje', 'danger');
            }

            $this->recalcularTotal($idFactura);

            DB::commit();

            return redirect()->route('facturas.edit', $idFactura)
                ->with('codigo_mensaje', 'M51')
                ->with('tipo_mensaje', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('codigo_mensaje', 'gen.error')
                ->with('tipo_mensaje', 'danger');
        }
    }

    /**
     * Función privada para evitar repetir código de sumatoria
     */
    private function recalcularTotal(string $idFactura)
    {
        $nuevoSubtotal = DB::table('proxfac')
            ->where('id_factura', $idFactura)
            ->where('estado_pxf', 'ACT')
            ->sum('pxf_subtotal');

        $nuevoIva = (int) round($nuevoSubtotal * 0.12);

        DB::table('facturas')
            ->where('id_factura', $idFactura)
            ->update([
                'fac_subtotal' => $nuevoSubtotal,
                'fac_iva' => $nuevoIva
            ]);
    }
}
