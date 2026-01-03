<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    /* =========================================================
       MENSAJERÍA DESDE config/mensajes.php
    ==========================================================*/
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

    /* =========================
       PANTALLA ÚNICA
    ==========================*/
    public function index(Request $request)
    {
        // ✅ per_page (pedido jefa)
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        // ✅ método estilo jefa (orden por estado + id) (ACT primero, INA al final) -> lo controlas en el Model
        $productos = Producto::obtenerParaLista($perPage);
        $productos->appends($request->except('page'));

        $unidades = UnidadMedida::listar();

        /* =========================
           EDITAR (solo si está ACT)
        ==========================*/
        $editId = $request->get('edit');
        $productoEditar = null;

        if ($editId) {
            $productoEditar = Producto::buscarPorId($editId);

            if (!$productoEditar) {
                return redirect()->route('productos.index')
                    ->with('error', $this->msg('gen.error'));
            }

            // ✅ BLOQUEO: si está INA no se puede editar
            if ($productoEditar->estado_prod === 'INA') {
                return redirect()->route('productos.index')
                    ->with('error', $this->msg('M60')); // operación no permitida
            }
        }

        /* =========================
           ELIMINAR (solo confirma)
        ==========================*/
        $deleteId = $request->get('delete');
        $productoEliminar = null;

        if ($deleteId) {
            $productoEliminar = Producto::buscarPorId($deleteId);

            if (!$productoEliminar) {
                return redirect()->route('productos.index')
                    ->with('error', $this->msg('gen.error'));
            }
        }

        /* =========================
           VISUALIZAR (solo lectura)
        ==========================*/
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
            'productoEditar' => $productoEditar,
            'productoEliminar' => $productoEliminar,
            'productoVer' => $productoVer,
            // si no hay registros en la lista principal, usamos "Sin resultados"
            'info' => $productos->count() === 0 ? $this->msg('M59') : null,
        ]);
    }

    /* =========================
       GUARDAR PRODUCTO (F4.1)
    ==========================*/
    public function store(Request $request)
    {
        if (!$request->pro_descripcion) {
            return back()->withErrors([
                'pro_descripcion' => $this->msg('M25') // Producto vacío
            ])->withInput();
        }

        if ($request->pro_precio_venta === null || $request->pro_precio_venta === '') {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M29') // Precio vacío
            ])->withInput();
        }

        if (!is_numeric($request->pro_precio_venta)) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M30') // Precio no numérico
            ])->withInput();
        }

        if ($request->pro_precio_venta < 0) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M31') // Precio negativo
            ])->withInput();
        }

        if (
            $request->pro_valor_compra !== null &&
            $request->pro_valor_compra !== '' &&
            $request->pro_valor_compra < 0
        ) {
            return back()->withErrors([
                'pro_valor_compra' => $this->msg('M31') // Precio negativo
            ])->withInput();
        }

        if (
            $request->pro_saldo_inicial === null ||
            $request->pro_saldo_inicial === '' ||
            $request->pro_saldo_inicial < 0
        ) {
            return back()->withErrors([
                'pro_saldo_inicial' => $this->msg('M35') // Cantidad inválida (para stock)
            ])->withInput();
        }

        if (Producto::existeDescripcion($request->pro_descripcion)) {
            return back()->withErrors([
                'pro_descripcion' => $this->msg('M26') // Producto duplicado
            ])->withInput();
        }

        try {
            $nuevoId = Producto::generarSiguienteId();

            $data = $request->all();
            $data['id_producto'] = $nuevoId;

            Producto::crearProductoTx($data);

            return redirect()->route('productos.index')
                ->with('ok', $this->msg('M1')); // Registro creado

        } catch (\Exception $e) {

            Log::error('ProductoController@store ERROR', [
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

    /* =========================
       ACTUALIZAR PRODUCTO (F4.2)
    ==========================*/
    public function update(Request $request, $id)
    {
        $producto = Producto::buscarPorId($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('gen.error'));
        }

        // ✅ BLOQUEO: si está INA no se puede editar
        if ($producto->estado_prod === 'INA') {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('M60'));
        }

        if ($request->pro_precio_venta === null || $request->pro_precio_venta === '') {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M29') // Precio vacío
            ])->withInput();
        }

        if (!is_numeric($request->pro_precio_venta)) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M30') // Precio no numérico
            ])->withInput();
        }

        if ($request->pro_precio_venta < 0) {
            return back()->withErrors([
                'pro_precio_venta' => $this->msg('M31') // Precio negativo
            ])->withInput();
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
                    'stock' => $this->msg('M35') // Cantidad inválida
                ])->withInput();
            }
        }

        try {
            $data = $request->all();
            $producto->actualizarProductoTx($data);

            return redirect()->route('productos.index')
                ->with('ok', $this->msg('M2')); // Registro actualizado

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

    /* =========================
       ELIMINAR (INACTIVAR) (F4.3)
    ==========================*/
    public function destroy($id)
    {
        $producto = Producto::buscarPorId($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('gen.error'));
        }

        // ✅ Si no está ACT, no se puede inactivar otra vez
        if ($producto->estado_prod !== 'ACT') {
            return redirect()->route('productos.index')
                ->with('error', $this->msg('M60'));
        }

        try {
            $producto->inactivarProductoTx();

            return redirect()->route('productos.index')
                ->with('ok', $this->msg('M3')); // Registro inactivado (mensaje genérico "eliminado")

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

    /* =========================
       CONSULTA POR PARÁMETRO (F4.4.2)
    ==========================*/
    public function buscar(Request $request)
    {
        // ✅ per_page también aquí (para que el selector sirva en búsqueda)
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $orden     = $request->input('orden');
        $categoria = $request->input('pro_categoria');
        $unidad    = $request->input('unidad_medida');

        $tieneOrden     = ($orden !== null && $orden !== '');
        $tieneCategoria = ($categoria !== null && $categoria !== '');
        $tieneUnidad    = ($unidad !== null && $unidad !== '');

        if (!$tieneOrden && !$tieneCategoria && !$tieneUnidad) {
            return back()->withErrors([
                'parametros' => $this->msg('M57') // Parámetro vacío
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
                    'orden' => $this->msg('M58') // Parámetro inválido
                ])->withInput();
            }

            $productos->appends($request->except('page'));

            $unidades = UnidadMedida::listar();

            return $this->viewWithMsgs('productos.index', [
                'productos' => $productos,
                'unidades' => $unidades,
                'productoEditar' => null,
                'productoEliminar' => null,
                'productoVer' => null,
                'info' => $productos->count() === 0 ? $this->msg('M59') : null, // Sin resultados
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
