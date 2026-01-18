<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Proxoc;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $parametro = $request->get('parametro');
        $valor     = trim((string) $request->get('valor'));
        $orden = $request->get('orden', 'fecha'); // fecha|estado|proveedor
        $ordenes = Compra::buscar($parametro, $valor, $orden);


        // Modal eliminar (misma pantalla): /compras?delete=OC-00001
        $compraDelete = null;
        if ($request->filled('delete')) {
            $id = trim((string) $request->get('delete'));

            $compraDelete = Compra::where('id_compra', $id)->first();

            if (!$compraDelete) {
                return redirect()
                    ->route('compras.index')
                    ->with('codigo_mensaje', 'M1')
                    ->with('tipo_mensaje', 'warning');
            }
        }

        return view('compras.index', compact(
            'ordenes', 'parametro', 'valor', 'orden', 'compraDelete'
        ));
    }
    public function create()
    {
        $proveedores = Proveedor::obtenerActivos();
        $productos = Producto::obtenerActivos();

        $idCompra = Compra::generarIdSugerido();

        return view('compras.create', compact('proveedores', 'productos', 'idCompra'));
    }
    public function store(Request $request)
    {
        // Validación con el formato REAL del blade:
        // productos[0][id_producto], productos[0][cantidad]
        $data = $request->validate([
            'id_proveedor' => ['required', 'string'], // PRV0002
            'accion'       => ['nullable', 'in:guardar,aprobar'],
            'productos'    => ['required', 'array', 'min:1'],
            'productos.*.id_producto' => ['required', 'string'],
            'productos.*.cantidad'    => ['required', 'integer', 'min:1'],
        ]);

        // Armar arrays para la SP
        $idsProductos = [];
        $cantidades   = [];

        foreach ($data['productos'] as $item) {
            $idProd = trim($item['id_producto']);
            $cant   = (int) $item['cantidad'];

            if ($idProd === '') {
                continue;
            }

            $idsProductos[] = $idProd;
            $cantidades[]   = $cant;
        }

        if (count($idsProductos) === 0) {
            return back()
                ->withErrors(['productos' => 'La orden de compra debe contener al menos un producto.'])
                ->withInput();
        }

        // Valores (precio compra) se obtienen desde BD, en el mismo orden
        $valores = \App\Models\Producto::obtenerValoresCompraPorIds($idsProductos);

        // Llamar SP para crear OC
        try {
            $idOc = \App\Models\Compra::spCrear(
                trim($data['id_proveedor']),
                now(),
                $idsProductos,
                $cantidades,
                $valores
            );
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }

        // Si eligió "Guardar y aprobar"
        $accion = $data['accion'] ?? 'guardar';
        if ($accion === 'aprobar') {
            try {
                \App\Models\Compra::spAprobar($idOc);
                return redirect()->route('compras.index')
                    ->with('codigo_mensaje', 'M39')
                    ->with('tipo_mensaje', 'success');
            } catch (\Throwable $e) {
                // OC queda creada (ABI), pero no aprobó
                return redirect()->route('compras.index')
                    ->with('warning', 'La OC se guardó, pero no se pudo aprobar: ' . $e->getMessage());
            }
        }

        // Guardado normal
        return redirect()->route('compras.index')
            ->with('codigo_mensaje', 'M39')
            ->with('tipo_mensaje', 'success');
    }

    public function edit(string $id)
    {
        $compra = \App\Models\Compra::where('id_compra', $id)->first();

        if (!$compra) {
            return redirect()->route('compras.index')
                ->with('error', 'Orden no encontrada.');
        }

        if (trim($compra->estado_oc) !== 'ABI') {
            return redirect()->route('compras.index')
                ->with('codigo_mensaje', 'M60')
                ->with('tipo_mensaje', 'warning');
        }

        $detalle = \DB::table('proxoc')
            ->where('id_compra', $id)
            ->orderBy('id_producto')
            ->get();

        $proveedores = \App\Models\Proveedor::where('estado_prv', 'ACT')
            ->orderByRaw("CAST(regexp_replace(id_proveedor, '\\D', '', 'g') AS INTEGER) ASC")
            ->get();

        $productos = \App\Models\Producto::where('estado_prod', 'ACT')
            ->orderByRaw("CAST(regexp_replace(id_producto, '\\D', '', 'g') AS INTEGER) ASC")
            ->get();

        return view('compras.edit', compact('compra', 'detalle', 'proveedores', 'productos'));
    }
    public function update(Request $request, string $id)
    {
        // Validación del formato del blade (productos[i][id_producto], productos[i][cantidad])
        $data = $request->validate([
            'id_proveedor' => ['required', 'string'],
            'accion'       => ['nullable', 'in:guardar,aprobar'],
            'productos'    => ['required', 'array', 'min:1'],
            'productos.*.id_producto' => ['required', 'string'],
            'productos.*.cantidad'    => ['required', 'integer', 'min:1'],
        ]);

        if (empty($data['id_proveedor'])) {
            return back()
                ->with('codigo_mensaje', 'M37')
                ->with('tipo_mensaje', 'warning')
                ->withInput();
        }

        $idsProductos = [];
        $cantidades   = [];

        foreach ($data['productos'] as $item) {
            $idProd = trim($item['id_producto']);
            $cant   = (int) $item['cantidad'];

            if ($idProd === '') continue;

            $idsProductos[] = $idProd;
            $cantidades[]   = $cant;
        }

        if (count($idsProductos) === 0) {
            return back()
                ->with('codigo_mensaje', 'M38')
                ->with('tipo_mensaje', 'warning')
                ->withInput();
        }

        $valores = \App\Models\Producto::obtenerValoresCompraPorIds($idsProductos);

        try {
            // Actualizar (solo si ABI, el SP lo valida)
            \App\Models\Compra::spActualizar(
                $id,
                trim($data['id_proveedor']),
                now(),
                $idsProductos,
                $cantidades,
                $valores
            );
        } catch (\Throwable $e) {
            // Si el SP lanza por estado, esto es tu M60 (A2)
            if (str_contains($e->getMessage(), 'estado actual')) {
                return redirect()->route('compras.index')
                    ->with('codigo_mensaje', 'M60')
                    ->with('tipo_mensaje', 'warning');
            }

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }

        $accion = $data['accion'] ?? 'guardar';
        if ($accion === 'aprobar') {
            try {
                \App\Models\Compra::spAprobar($id);
                return redirect()->route('compras.index')
                    ->with('codigo_mensaje', 'M2')
                    ->with('tipo_mensaje', 'success');
            } catch (\Throwable $e) {
                return redirect()->route('compras.index')
                    ->with('warning', 'La OC se actualizó, pero no se pudo aprobar: ' . $e->getMessage());
            }
        }

        return redirect()->route('compras.index')
            ->with('codigo_mensaje', 'M2')
            ->with('tipo_mensaje', 'success');
    }


    public function eliminar(string $id)
    {
        $compra = Compra::obtenerPorId($id);

        if (!$compra) {
            abort(404);
        }

        return view('compras.eliminar', compact('compra'));
    }

    public function destroy(string $id)
    {
        $compra = Compra::obtenerPorId($id);

        if (!$compra) {
            abort(404);
        }

        if ($compra->estado_oc !== 'ABI') {
            return redirect()
                ->route('compras.index')
                ->with('codigo_mensaje', 'M60')
                ->with('tipo_mensaje', 'warning');
        }

        try {
            Compra::spAnular($id);

            return redirect()
                ->route('compras.index')
                ->with('codigo_mensaje', 'M40')
                ->with('tipo_mensaje', 'success');
        } catch (\Throwable $e) {
            return redirect()
                ->route('compras.index')
                ->with('error', $e->getMessage());
        }
    }

    public function aprobar(string $id)
    {
        $compra = Compra::obtenerPorId($id);

        if (!$compra) {
            abort(404);
        }

        if ($compra->estado_oc !== 'ABI') {
            return redirect()
                ->route('compras.index')
                ->with('codigo_mensaje', 'M60')
                ->with('tipo_mensaje', 'warning');
        }

        try {
            Compra::spAprobar($id);

            return redirect()
                ->route('compras.index')
                ->with('ok', 'Orden aprobada correctamente.')
                ->with('tipo_mensaje', 'success');
        } catch (\Throwable $e) {
            return redirect()
                ->route('compras.index')
                ->with('error', $e->getMessage());
        }
    }
}
