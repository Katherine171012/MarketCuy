<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Factura extends Model
{
    protected $table = 'facturas';
    protected $primaryKey = 'id_factura';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_factura',
        'id_cliente',
        'fac_descripcion',
        'fac_fecha_hora',
        'fac_subtotal',
        'fac_iva',
        'estado_fac'
    ];

    /* ======================
     * RELACIONES
     * ====================== */

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'id_factura', 'id_factura');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /* ======================
 * MÉTODOS DE CÁLCULO
 * ====================== */

    /**
     * Constante para IVA (Ecuador 12%)
     */
    const IVA_PORCENTAJE = 0.12;

    /**
     * Calcular subtotal desde detalles
     */
    public function calcularSubtotal(): float
    {
        return $this->detalles->sum('pxf_subtotal');
    }

    /**
     * Calcular IVA
     */
    public function calcularIva(): float
    {
        return round($this->calcularSubtotal() * self::IVA_PORCENTAJE, 2);
    }

    /**
     * Calcular total
     */
    public function calcularTotal(): float
    {
        return $this->calcularSubtotal() + $this->calcularIva();
    }

    /* ======================
     * MÉTODOS DE ESTADO
     * ====================== */

    /**
     * Verificar si la factura es editable
     */
    public function esEditable(): bool
    {
        return $this->estado_fac === 'ABI';
    }

    /**
     * Obtener texto legible del estado
     */
    public function getEstadoTextoAttribute(): string
    {
        return match($this->estado_fac) {
            'ABI' => msg('facturas.estado.abierta'),
            'APR' => msg('facturas.estado.aprobada'),
            'ANU' => msg('facturas.estado.anulada'),
            default => $this->estado_fac
        };
    }

    /* ======================
     * LÓGICA (estilo Alumno)
     * ====================== */

    /**
     * F5.4.1 – Consulta general
     */
    /**
     * F5.4.1 – Consulta general (con orden + paginación dinámica)
     */
    public static function obtenerListado(
        ?string $sort = null,
        ?string $dir = 'asc',
        int $porPagina = 10
    ) {
        $query = DB::table('vw_facturas_totales as f')
            ->join('clientes as c', 'c.id_cliente', '=', 'f.id_cliente')
            ->select(
                'f.id_factura',
                'c.cli_nombre',
                'f.fac_fecha_hora',
                'f.subtotal as fac_subtotal',
                'f.iva as fac_iva',
                'f.total',
                'f.estado_fac'
            );

        // Columnas permitidas para ordenar (SEGURIDAD)
        $columnasPermitidas = [
            'id_factura' => 'f.id_factura',
            'cliente'    => 'c.cli_nombre',
            'fecha'      => 'f.fac_fecha_hora',
            'subtotal'   => 'f.subtotal',
            'total'      => 'f.total',
            'estado'     => 'f.estado_fac',
        ];

        $dir = ($dir === 'desc') ? 'desc' : 'asc';

        // 1️⃣ PRIORIDAD POR ESTADO (PRIMERO)
        $query->orderByRaw("
        CASE
            WHEN f.estado_fac = 'ABI' THEN 1
            WHEN f.estado_fac = 'APR' THEN 2
            ELSE 3
        END
    ");

        // 2️⃣ ORDEN SECUNDARIO (FECHA O COLUMNA)
        if ($sort && isset($columnasPermitidas[$sort])) {
            $query->orderBy($columnasPermitidas[$sort], $dir);
        } else {
            // orden por defecto: MÁS NUEVAS PRIMERO
            $query->orderBy('f.fac_fecha_hora', 'desc');
        }

        return $query
            ->paginate($porPagina)
            ->withQueryString();
    }

    /**
     * F5.4.2 – Búsqueda por parámetros (para INDEX con paginación)
     */
    public static function buscarPorParametrosIndex(array $params, int $porPagina = 10)
    {
        $query = DB::table('vw_facturas_totales as f')
            ->join('clientes as c', 'c.id_cliente', '=', 'f.id_cliente')
            ->select(
                'f.id_factura',
                'c.cli_nombre',
                'f.fac_fecha_hora',
                'f.subtotal as fac_subtotal',
                'f.iva as fac_iva',
                'f.total',
                'f.estado_fac'
            );

        // N° factura
        if (!empty($params['id_factura'])) {
            $query->where('f.id_factura', $params['id_factura']);
        }

        // Cliente (texto libre)
        if (!empty($params['cliente'])) {
            $texto = trim($params['cliente']);

            $query->where(function ($q) use ($texto) {
                $q->where('c.cli_nombre', 'ILIKE', "%{$texto}%")
                    ->orWhere('f.id_cliente', $texto);
            });
        }

        // Estado
        if (!empty($params['estado_fac'])) {
            $query->where('f.estado_fac', $params['estado_fac']);
        }

        // Fecha desde
        if (!empty($params['fecha_desde'])) {
            $query->whereDate('f.fac_fecha_hora', '>=', $params['fecha_desde']);
        }

        // Fecha hasta
        if (!empty($params['fecha_hasta'])) {
            $query->whereDate('f.fac_fecha_hora', '<=', $params['fecha_hasta']);
        }

        // Orden por defecto (más recientes primero)
        $query->orderBy('f.fac_fecha_hora', 'desc');

        return $query
            ->paginate($porPagina)
            ->withQueryString();
    }






    /**
     * Generar siguiente ID tipo FAC0001
     */
    public static function generarSiguienteId(): string
    {
        $maxNum = self::whereRaw("id_factura ~ '^FCT[0-9]+$'")
            ->selectRaw("MAX(CAST(SUBSTRING(id_factura FROM 4) AS INTEGER)) AS max_num")
            ->value('max_num');

        $nextNum = ($maxNum !== null) ? ((int)$maxNum + 1) : 1;

        return 'FCT' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }


    /**
     * F5.1 – Crear factura
     */
    public static function crearFactura(string $idCliente, ?string $descripcion, array $productos)
    {
        DB::beginTransaction();

        try {

            $idFactura = self::generarSiguienteId();

            $factura = self::create([
                'id_factura'     => $idFactura,
                'id_cliente'     => $idCliente,
                'fac_descripcion'=> $descripcion,
                'fac_fecha_hora' => now(),
                'fac_subtotal'   => 0,
                'fac_iva'        => 0,
                'estado_fac'     => 'ABI'
            ]);

            $subtotalFactura = 0;

            foreach ($productos as $item) {

                if (empty($item['id_producto']) || empty($item['cantidad'])) {
                    continue;
                }

                $producto = Producto::where('id_producto', $item['id_producto'])
                    ->where('estado_prod', 'ACT')
                    ->lockForUpdate()
                    ->first();

                if (!$producto) {
                    throw new \Exception(msg('facturas.producto.no_disponible'));
                }

                if ($item['cantidad'] > $producto->pro_saldo_final) {
                    throw new \Exception(msg('facturas.cantidad.mayor_stock'));
                }

                $subtotal = $item['cantidad'] * $producto->pro_precio_venta;

                DetalleFactura::create([
                    'id_factura'   => $idFactura,
                    'id_producto'  => $producto->id_producto,
                    'pxf_cantidad' => $item['cantidad'],
                    'pxf_precio'   => $producto->pro_precio_venta,
                    'pxf_subtotal' => $subtotal,
                    'estado_pxf'   => 'ACT'
                ]);

                $producto->pro_qty_egresos += $item['cantidad'];
                $producto->pro_saldo_final -= $item['cantidad'];
                $producto->save();

                $subtotalFactura += $subtotal;
            }

            if ($subtotalFactura <= 0) {
                throw new \Exception(msg('facturas.sin_productos'));
            }

            $factura->fac_subtotal = $subtotalFactura;
            $factura->fac_iva = (int) round($subtotalFactura * 0.12);
            $factura->save();

            DB::commit();
            return $factura;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function aprobarFactura(string $idFactura)
    {
        DB::beginTransaction();

        try {
            $factura = self::lockForUpdate()->findOrFail($idFactura);

            if ($factura->estado_fac !== 'ABI') {
                throw new \Exception(msg('facturas.no_aprobable'));
            }

            $factura->estado_fac = 'APR';
            $factura->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerParaLista($porPagina = 10)
    {
        return self::with('ciudad')
            ->orderByRaw("CASE
            WHEN estado_cli = 'ACT' THEN 1
            WHEN estado_cli = 'SUS' THEN 2
            ELSE 3 END")
            ->orderBy('id_cliente', 'asc')
            ->paginate($porPagina);
    }


    /**
     * F5.2 – Obtener factura para edición
     */
    public static function obtenerParaEdicion(string $idFactura)
    {
        return self::with(['detalles.producto', 'cliente'])
            ->findOrFail($idFactura);
    }

    /**
     * F5.2 – Modificar factura
     */
    public static function modificarFactura(string $idFactura, ?string $descripcion, array $productos)
    {
        DB::beginTransaction();

        try {

            $factura = self::with('detalles')
                ->lockForUpdate()
                ->findOrFail($idFactura);

            if ($factura->estado_fac !== 'ABI') {
                throw new \Exception(msg('facturas.no_editable'));
            }

            // Revertir inventario anterior
            foreach ($factura->detalles as $detalle) {

                $producto = Producto::where('id_producto', $detalle->id_producto)
                    ->lockForUpdate()
                    ->first();

                $producto->pro_qty_egresos -= $detalle->pxf_cantidad;
                $producto->pro_saldo_final += $detalle->pxf_cantidad;
                $producto->save();

                DetalleFactura::where('id_factura', $detalle->id_factura)
                    ->where('id_producto', $detalle->id_producto)
                    ->delete();
            }

            $subtotalFactura = 0;

            foreach ($productos as $item) {

                if (empty($item['id_producto']) || empty($item['cantidad'])) {
                    continue;
                }

                $producto = Producto::where('id_producto', $item['id_producto'])
                    ->lockForUpdate()
                    ->first();

                if ($item['cantidad'] > $producto->pro_saldo_final) {
                    throw new \Exception(msg('facturas.cantidad.mayor_stock'));
                }

                $subtotal = $item['cantidad'] * $producto->pro_precio_venta;

                DetalleFactura::create([
                    'id_factura'   => $factura->id_factura,
                    'id_producto'  => $producto->id_producto,
                    'pxf_cantidad' => $item['cantidad'],
                    'pxf_precio'   => $producto->pro_precio_venta,
                    'pxf_subtotal' => $subtotal,
                    'estado_pxf'   => 'ACT'
                ]);

                $producto->pro_qty_egresos += $item['cantidad'];
                $producto->pro_saldo_final -= $item['cantidad'];
                $producto->save();

                $subtotalFactura += $subtotal;
            }

            if ($subtotalFactura <= 0) {
                throw new \Exception(msg('facturas.sin_productos'));
            }

            $factura->fac_descripcion = $descripcion;
            $factura->fac_subtotal = $subtotalFactura;
            $factura->fac_iva = (int) round($subtotalFactura * 0.12);
            $factura->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * F5.3 – Anular factura
     */
    public static function anularFactura(string $idFactura)
    {
        DB::beginTransaction();

        try {

            $factura = self::with('detalles')
                ->lockForUpdate()
                ->findOrFail($idFactura);

            if ($factura->estado_fac !== 'ABI') {
                throw new \Exception(msg('facturas.no_anulable'));
            }

            foreach ($factura->detalles as $detalle) {

                $producto = Producto::where('id_producto', $detalle->id_producto)
                    ->lockForUpdate()
                    ->first();

                $producto->pro_qty_egresos -= $detalle->pxf_cantidad;
                $producto->pro_saldo_final += $detalle->pxf_cantidad;
                $producto->save();

                $detalle->update(['estado_pxf' => 'INA']);
            }

            $factura->estado_fac = 'ANU';
            $factura->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * F5.4.2 – Consultar facturas por parámetros
     */
    public static function buscarPorParametros(array $params)
    {
        if (
            empty($params['id_factura']) &&
            empty($params['cliente']) &&
            empty($params['estado_fac']) &&
            empty($params['fecha_desde']) &&
            empty($params['fecha_hasta'])
        ) {
            throw new \Exception(msg('facturas.parametros.vacios'));
        }

        $query = DB::table('vw_facturas_totales as f')
            ->join('clientes as c', 'c.id_cliente', '=', 'f.id_cliente')
            ->select(
                'f.id_factura',
                'c.cli_nombre',
                'f.fac_fecha_hora',
                'f.subtotal as fac_subtotal',
                'f.iva as fac_iva',
                'f.total',
                'f.estado_fac'
            );

        // N° factura
        if (!empty($params['id_factura'])) {
            $query->where('f.id_factura', $params['id_factura']);
        }

        // Cliente (texto libre)
        if (!empty($params['cliente'])) {
            $texto = trim($params['cliente']);

            $query->where(function ($q) use ($texto) {
                $q->where('c.cli_nombre', 'ILIKE', "%{$texto}%")
                    ->orWhere('f.id_cliente', $texto);
            });
        }

        // Estado
        if (!empty($params['estado_fac'])) {
            $query->where('f.estado_fac', $params['estado_fac']);
        }

        // Fecha desde
        if (!empty($params['fecha_desde'])) {
            $query->whereDate('f.fac_fecha_hora', '>=', $params['fecha_desde']);
        }

        // Fecha hasta
        if (!empty($params['fecha_hasta'])) {
            $query->whereDate('f.fac_fecha_hora', '<=', $params['fecha_hasta']);
        }

        $resultados = $query
            ->orderBy('f.fac_fecha_hora', 'desc')
            ->get();

        if ($resultados->isEmpty()) {
            throw new \Exception(msg('facturas.sin_resultados'));
        }

        return $resultados;
    }

    /* ======================
 * MÉTODOS PARA VISTAS
 * ====================== */

    /**
     * Obtener badge HTML del estado
     */
    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado_fac) {
            'ABI' => '<span class="badge bg-warning text-dark">'.msg('facturas.estado.abierta').'</span>',
            'APR' => '<span class="badge bg-success">'.msg('facturas.estado.aprobada').'</span>',
            'ANU' => '<span class="badge bg-danger">'.msg('facturas.estado.anulada').'</span>',
            default => '<span class="badge bg-secondary">'.$this->estado_fac.'</span>'
        };
    }

    /**
     * Formatear fecha para mostrar
     */
    public function getFechaFormateadaAttribute(): string
    {
        return \Carbon\Carbon::parse($this->fac_fecha_hora)->format('d/m/Y H:i');
    }





}
