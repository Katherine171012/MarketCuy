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

    private function flash(string $codigo, string $tipo = 'success')
    {
        session()->flash('codigo_mensaje', $codigo);
        session()->flash('tipo_mensaje', $tipo);
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
                $this->flash('gen.error', 'danger');
                return redirect()->route('productos.index');
            }

            if ($productoEditar->estado_prod === 'INA') {
                $this->flash('M60', 'danger');
                return redirect()->route('productos.index');
            }
        }

        $deleteId = $request->get('delete');
        $productoEliminar = null;

        if ($deleteId) {
            $productoEliminar = Producto::buscarPorId($deleteId);

            if (!$productoEliminar) {
                $this->flash('gen.error', 'danger');
                return redirect()->route('productos.index');
            }
        }

        $viewId = $request->get('view');
        $productoVer = null;

        if ($viewId) {
            $productoVer = Producto::buscarPorId($viewId);

            if (!$productoVer) {
                $this->flash('gen.error', 'danger');
                return redirect()->route('productos.index');
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

        if ((float)$request->pro_precio_venta <= 0) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('productos.precio.mayor_cero')
            ])->withInput();
        }

        $etiqueta = trim((string) ($request->pro_etiqueta ?? ''));
        $esOferta = (mb_strtolower($etiqueta) === 'oferta');

        if ($esOferta) {
            return back()->withErrors([
                'pro_etiqueta' => $this->msg('productos.oferta.creacion')
            ])->withInput();
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

        if ($request->pro_saldo_inicial === null || $request->pro_saldo_inicial === '' || (int)$request->pro_saldo_inicial < 0) {
            return back()->withErrors([
                'pro_saldo_inicial' => $this->msg('M35')
            ])->withInput();
        }

        if ($request->hasFile('pro_imagen')) {
            $file = $request->file('pro_imagen');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'pdf'], true)) {
                return back()->withErrors([
                    'pro_imagen' => $this->msg('productos.imagen.formato')
                ])->withInput();
            }
        }

        try {
            $nuevoId = Producto::generarSiguienteId();
            $saldoInicial = (int) $request->pro_saldo_inicial;

            $data = [
                'id_producto'       => $nuevoId,
                'pro_nombre'        => $nombre,
                'pro_descripcion'   => $request->pro_descripcion !== '' ? $request->pro_descripcion : null,
                'unidad_medida'     => $request->unidad_medida,
                'pro_valor_compra'  => $request->pro_valor_compra ?? 0,
                'pro_precio_venta'  => $request->pro_precio_venta,
                'pro_precio_antes'  => null,
                'id_categoria'      => $request->id_categoria ?: null,
                'pro_etiqueta'      => $etiqueta !== '' ? $etiqueta : null,
                'pro_es_destacado'  => $request->has('pro_es_destacado') ? true : false,
                'pro_clicks_count'  => 0,

                'pro_saldo_inicial' => $saldoInicial,
                'pro_qty_ingresos'  => 0,
                'pro_qty_egresos'   => 0,
                'pro_qty_ajustes'   => 0,

                'pro_saldo_final'   => $saldoInicial,
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

            $this->flash('productos.crear.ok', 'success');
            return redirect()->route('productos.index');

        } catch (\Exception $e) {

            Log::error('ProductoController@store ERROR', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->flash('gen.error', 'danger');
            return back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::buscarPorId($id);

        if (!$producto) {
            $this->flash('gen.error', 'danger');
            return redirect()->route('productos.index');
        }

        if ($producto->estado_prod === 'INA') {
            $this->flash('M60', 'danger');
            return redirect()->route('productos.index');
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

        if ((float)$request->pro_precio_venta <= 0) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('productos.precio.mayor_cero')
            ])->withInput();
        }

        $precioVentaAnterior = (float) $producto->pro_precio_venta;
        $precioVentaNuevo    = (float) $request->pro_precio_venta;

        $etiqueta = trim((string) ($request->pro_etiqueta ?? ''));
        $tieneEtiqueta = ($etiqueta !== '');
        $esOferta = (mb_strtolower($etiqueta) === 'oferta');

        if ($tieneEtiqueta && $esOferta && $precioVentaNuevo >= $precioVentaAnterior) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('productos.oferta.descuento')
            ])->withInput();
        }

        if ($request->hasFile('pro_imagen')) {
            $file = $request->file('pro_imagen');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'pdf'], true)) {
                return back()->withErrors([
                    'pro_imagen' => $this->msg('productos.imagen.formato')
                ])->withInput();
            }
        }

        try {
            $data = [
                'pro_descripcion'   => $request->pro_descripcion !== '' ? $request->pro_descripcion : null,
                'id_categoria'      => $request->id_categoria ?: null,
                'pro_valor_compra'  => $request->pro_valor_compra ?? $producto->pro_valor_compra,
                'pro_precio_venta'  => $precioVentaNuevo,
                'pro_etiqueta'      => $tieneEtiqueta ? $etiqueta : null,
                'pro_es_destacado'  => $request->has('pro_es_destacado') ? true : false,
            ];

            if ($tieneEtiqueta && $esOferta) {
                $data['pro_precio_antes'] = $precioVentaAnterior;
            } else {
                $data['pro_precio_antes'] = null;
            }

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

            $this->flash('productos.actualizar.ok', 'success');
            return redirect()->route('productos.index');

        } catch (\Exception $e) {

            Log::error('ProductoController@update ERROR', [
                'id_producto' => $id,
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->flash('gen.error', 'danger');
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $producto = Producto::buscarPorId($id);

        if (!$producto) {
            $this->flash('gen.error', 'danger');
            return redirect()->route('productos.index');
        }

        if ($producto->estado_prod !== 'ACT') {
            $this->flash('M60', 'danger');
            return redirect()->route('productos.index');
        }

        try {
            $producto->inactivarProductoTx();

            $this->flash('productos.eliminar.ok', 'success');
            return redirect()->route('productos.index');

        } catch (\Exception $e) {

            Log::error('ProductoController@destroy ERROR', [
                'id_producto' => $id,
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->flash('gen.error', 'danger');
            return redirect()->route('productos.index');
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

        $mapOrden = [
            'id_asc'   => null,
            'id_desc'  => null,
            'desc_az'  => 'nombre_asc',
            'desc_za'  => 'nombre_desc',
            'precio_asc'  => 'precio_asc',
            'precio_desc' => 'precio_desc',
            'nombre_asc'  => 'nombre_asc',
            'nombre_desc' => 'nombre_desc',
        ];

        $ordenFinal = $orden;
        if ($tieneOrden) {
            if (!array_key_exists($orden, $mapOrden)) {
                return back()->withErrors([
                    'orden' => $this->msg('M58')
                ])->withInput();
            }
            $ordenFinal = $mapOrden[$orden];
        }

        try {
            $productos = Producto::paginarActivosConFiltros(
                $ordenFinal,
                $categoria,
                $unidad,
                $perPage
            );

            if ($tieneOrden && ($orden === 'id_asc' || $orden === 'id_desc')) {
                $dir = ($orden === 'id_desc') ? 'DESC' : 'ASC';
                $productos = Producto::with(['categoria', 'unidad'])
                    ->where('estado_prod', 'ACT')
                    ->when($categoria, fn($q) => $q->where('id_categoria', $categoria))
                    ->when($unidad, fn($q) => $q->where('unidad_medida', $unidad))
                    ->orderBy('id_producto', $dir)
                    ->paginate($perPage);
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
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'params' => $request->all(),
            ]);

            $this->flash('gen.error', 'danger');
            return redirect()->route('productos.index');
        }
    }
}
