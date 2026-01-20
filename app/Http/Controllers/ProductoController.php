<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    private function msg(string $key): string
    {
        $all = config('mensajes', []);
        return $all[$key] ?? $key;
    }

    private function viewWithMsgs(string $view, array $data = [])
    {
        $data['msg'] = config('mensajes', []);
        return view($view, $data);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $productos = Producto::obtenerParaLista($perPage);
        $productos->appends($request->except('page'));

        $unidades = UnidadMedida::listar();
        $categorias = Categoria::listarActivas();

        $editId = $request->get('edit');
        $productoEditar = null;

        if ($editId) {
            $productoEditar = Producto::buscarPorId($editId);

            if (!$productoEditar) {
                return redirect()->route('productos.index')
                    ->with('error', $this->msg('gen.error'));
            }
            if ($productoEditar->estado_prod === 'INA') {
                return redirect()->route('productos.index')
                    ->with('error', $this->msg('M60'));
            }
        }

        $deleteId = $request->get('delete');
        $productoEliminar = null;

        if ($deleteId) {
            $productoEliminar = Producto::buscarPorId($deleteId);

            if (!$productoEliminar) {
                return redirect()->route('productos.index')
                    ->with('error', $this->msg('gen.error'));
            }
        }

        $viewId = $request->get('view');
        $productoVer = null;

        if ($viewId) {
            $productoVer = Producto::buscarPorId($viewId);

            if (!$productoVer) {
                return redirect()->route('productos.index')
                    ->with('error', $this->msg('gen.error'));
            }
        }

        return $this->viewWithMsgs('productos.index', [
            'productos' => $productos,
            'unidades' => $unidades,
            'categorias' => $categorias,
            'productoEditar' => $productoEditar,
            'productoEliminar' => $productoEliminar,
            'productoVer' => $productoVer,
            'info' => $productos->count() === 0 ? $this->msg('M59') : null,
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->pro_nombre) {
            return back()->withErrors([
                'pro_nombre' => $this->msg('M25')
            ])->withInput();
        }

        $nombre = trim($request->pro_nombre);

        if (Producto::existeNombre($nombre)) {
            return back()->withErrors([
                'pro_nombre' => $this->msg('M26')
            ])->withInput();
        }

        if ($request->pro_precio_venta === null || $request->pro_precio_venta === '') {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M29')
            ])->withInput();
        }

        if (!is_numeric($request->pro_precio_venta)) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M30')
            ])->withInput();
        }

        if ($request->pro_precio_venta < 0) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M31')
            ])->withInput();
        }

        $etiqueta = trim((string) ($request->pro_etiqueta ?? ''));
        $esOferta = ($etiqueta !== '') && str_contains(mb_strtolower($etiqueta), 'oferta');

        $precioVenta = (float) $request->pro_precio_venta;
        $precioAntesRaw = $request->pro_precio_antes;
        $tienePrecioAntes = !($precioAntesRaw === null || $precioAntesRaw === '');

        if ($esOferta && !$tienePrecioAntes) {
            return back()->withErrors([
                'pro_precio_antes' => 'Si la etiqueta es Oferta, debes ingresar el precio antes.'
            ])->withInput();
        }

        if ($tienePrecioAntes) {
            if (!is_numeric($precioAntesRaw)) {
                return back()->withErrors([
                    'pro_precio_antes' => 'El precio antes debe ser numérico.'
                ])->withInput();
            }
            if ((float)$precioAntesRaw < 0) {
                return back()->withErrors([
                    'pro_precio_antes' => 'El precio antes no puede ser negativo.'
                ])->withInput();
            }
            if ((float)$precioAntesRaw <= $precioVenta) {
                return back()->withErrors([
                    'pro_precio_antes' => 'El precio antes debe ser mayor al precio de venta.'
                ])->withInput();
            }
        }

        if (
            $request->pro_valor_compra !== null &&
            $request->pro_valor_compra !== '' &&
            $request->pro_valor_compra < 0
        ) {
            return back()->withErrors([
                'pro_valor_compra' => $this->msg('M31')
            ])->withInput();
        }

        if (
            $request->pro_saldo_inicial === null ||
            $request->pro_saldo_inicial === '' ||
            $request->pro_saldo_inicial < 0
        ) {
            return back()->withErrors([
                'pro_saldo_inicial' => $this->msg('M35')
            ])->withInput();
        }

        if ($request->hasFile('pro_imagen')) {
            $file = $request->file('pro_imagen');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'pdf'], true)) {
                return back()->withErrors([
                    'pro_imagen' => 'Solo se permiten archivos JPG o PDF.'
                ])->withInput();
            }
        }

        try {
            $nuevoId = Producto::generarSiguienteId();

            $data = [
                'id_producto'       => $nuevoId,
                'pro_nombre'        => $nombre,
                'pro_descripcion'   => $request->pro_descripcion !== '' ? $request->pro_descripcion : null,
                'unidad_medida'     => $request->unidad_medida,
                'pro_valor_compra'  => $request->pro_valor_compra ?? 0,
                'pro_precio_venta'  => $request->pro_precio_venta,
                'pro_saldo_inicial' => $request->pro_saldo_inicial,
                'pro_precio_antes'  => $tienePrecioAntes ? (float) $precioAntesRaw : null,
                'id_categoria'      => $request->id_categoria ?: null,

                'pro_etiqueta'      => $request->pro_etiqueta ?: null,
                'pro_es_destacado'  => $request->has('pro_es_destacado') ? true : false,

                'pro_clicks_count'  => 0,
            ];

            if ($request->hasFile('pro_imagen') && $request->file('pro_imagen')->isValid()) {
                $file = $request->file('pro_imagen');
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = $nuevoId . '.' . $ext;
                $path = $file->storeAs('productos', $filename, 'public');
                $data['pro_imagen'] = $path;
            } else {
                $data['pro_imagen'] = null;
            }

            Producto::crearProductoTx($data);

            return redirect()->route('productos.index')
                ->with('ok', $this->msg('M1'));

        } catch (\Exception $e) {

            Log::error('ProductoController@store ERROR', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', $this->msg('gen.error'))
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::buscarPorId($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('gen.error'));
        }

        if ($producto->estado_prod === 'INA') {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('M60'));
        }

        if ($request->pro_precio_venta === null || $request->pro_precio_venta === '') {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M29')
            ])->withInput();
        }

        if (!is_numeric($request->pro_precio_venta)) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M30')
            ])->withInput();
        }

        if ($request->pro_precio_venta < 0) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M31')
            ])->withInput();
        }

        $etiqueta = trim((string) ($request->pro_etiqueta ?? ''));
        $esOferta = ($etiqueta !== '') && str_contains(mb_strtolower($etiqueta), 'oferta');

        $precioVenta = (float) $request->pro_precio_venta;
        $precioAntesRaw = $request->pro_precio_antes;
        $tienePrecioAntes = !($precioAntesRaw === null || $precioAntesRaw === '');

        if ($esOferta && !$tienePrecioAntes) {
            return back()->withErrors([
                'pro_precio_antes' => 'Si la etiqueta es Oferta, debes ingresar el precio antes.'
            ])->withInput();
        }

        if ($tienePrecioAntes) {
            if (!is_numeric($precioAntesRaw)) {
                return back()->withErrors([
                    'pro_precio_antes' => 'El precio antes debe ser numérico.'
                ])->withInput();
            }
            if ((float)$precioAntesRaw < 0) {
                return back()->withErrors([
                    'pro_precio_antes' => 'El precio antes no puede ser negativo.'
                ])->withInput();
            }
            if ((float)$precioAntesRaw <= $precioVenta) {
                return back()->withErrors([
                    'pro_precio_antes' => 'El precio antes debe ser mayor al precio de venta.'
                ])->withInput();
            }
        }

        $nums = [
            'pro_saldo_inicial',
            'pro_qty_ingresos',
            'pro_qty_egresos',
            'pro_qty_ajustes',
            'pro_saldo_final'
        ];

        foreach ($nums as $n) {
            if ($request->$n < 0) {
                return back()->withErrors([
                    'stock' => $this->msg('M35')
                ])->withInput();
            }
        }

        if ($request->hasFile('pro_imagen')) {
            $file = $request->file('pro_imagen');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'pdf'], true)) {
                return back()->withErrors([
                    'pro_imagen' => 'Solo se permiten archivos JPG o PDF.'
                ])->withInput();
            }
        }

        try {
            $data = [
                'pro_descripcion'   => $request->pro_descripcion !== '' ? $request->pro_descripcion : null,
                'id_categoria'      => $request->id_categoria ?: null,

                'pro_valor_compra'  => $request->pro_valor_compra ?? $producto->pro_valor_compra,
                'pro_precio_venta'  => $request->pro_precio_venta,
                'pro_precio_antes'  => $tienePrecioAntes ? (float) $precioAntesRaw : null,

                'pro_etiqueta'      => $request->pro_etiqueta ?: null,
                'pro_es_destacado'  => $request->has('pro_es_destacado') ? true : false,

                'pro_saldo_inicial' => $request->pro_saldo_inicial,
                'pro_qty_ingresos'  => $request->pro_qty_ingresos,
                'pro_qty_egresos'   => $request->pro_qty_egresos,
                'pro_qty_ajustes'   => $request->pro_qty_ajustes,
                'pro_saldo_final'   => $request->pro_saldo_final,
            ];

            if ($request->hasFile('pro_imagen') && $request->file('pro_imagen')->isValid()) {
                if ($producto->pro_imagen && Storage::disk('public')->exists($producto->pro_imagen)) {
                    Storage::disk('public')->delete($producto->pro_imagen);
                }

                $file = $request->file('pro_imagen');
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = $producto->id_producto . '.' . $ext;
                $path = $file->storeAs('productos', $filename, 'public');
                $data['pro_imagen'] = $path;
            }

            $producto->actualizarProductoTx($data);

            return redirect()->route('productos.index')
                ->with('ok', $this->msg('M2'));

        } catch (\Exception $e) {

            Log::error('ProductoController@update ERROR', [
                'id_producto' => $id,
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', $this->msg('gen.error'))
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $producto = Producto::buscarPorId($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('gen.error'));
        }
        if ($producto->estado_prod !== 'ACT') {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('M60'));
        }

        try {
            $producto->inactivarProductoTx();

            return redirect()->route('productos.index')
                ->with('ok', $this->msg('M3'));

        } catch (\Exception $e) {

            Log::error('ProductoController@destroy ERROR', [
                'id_producto' => $id,
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('productos.index')
                ->with('error', $this->msg('gen.error'));
        }
    }

    public function buscar(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $orden     = $request->input('orden');
        $categoria = $request->input('id_categoria');
        $unidad    = $request->input('unidad_medida');

        $tieneOrden     = ($orden !== null && $orden !== '');
        $tieneCategoria = ($categoria !== null && $categoria !== '');
        $tieneUnidad    = ($unidad !== null && $unidad !== '');

        if (!$tieneOrden && !$tieneCategoria && !$tieneUnidad) {
            return back()->withErrors([
                'parametros' => $this->msg('M57')
            ])->withInput();
        }

        try {
            $productos = Producto::paginarActivosConFiltros(
                $orden,
                $categoria,
                $unidad,
                $perPage
            );

            if ($productos === null) {
                return back()->withErrors([
                    'orden' => $this->msg('M58')
                ])->withInput();
            }

            $productos->appends($request->except('page'));

            $unidades = UnidadMedida::listar();
            $categorias = Categoria::listarActivas();

            return $this->viewWithMsgs('productos.index', [
                'productos' => $productos,
                'unidades' => $unidades,
                'categorias' => $categorias,
                'productoEditar' => null,
                'productoEliminar' => null,
                'productoVer' => null,
                'info' => $productos->count() === 0 ? $this->msg('M59') : null,
            ]);

        } catch (\Exception $e) {

            Log::error('ProductoController@buscar ERROR', [
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'params' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('productos.index')
                ->with('error', $this->msg('gen.error'));
        }
    }
}
