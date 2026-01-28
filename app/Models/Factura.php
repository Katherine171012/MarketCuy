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
        'estado_fac',
        'canal_venta', // ← obligatorio por BD
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
        return match ($this->estado_fac) {
            'ABI' => 'Abierta',
            'APR' => 'Aprobada',
            'ANU' => 'Anulada',
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
            'cliente' => 'c.cli_nombre',
            'fecha' => 'f.fac_fecha_hora',
            'subtotal' => 'f.subtotal',
            'total' => 'f.total',
            'estado' => 'f.estado_fac',
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

        $nextNum = ($maxNum !== null) ? ((int) $maxNum + 1) : 1;

        return 'FCT' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * F5.1 – Crear SOLO cabecera de factura
     */
    public static function crearCabecera(string $idCliente, ?string $descripcion): self
    {
        DB::beginTransaction();

        try {

            $idFactura = self::generarSiguienteId();

            $factura = self::create([
                'id_factura' => $idFactura,
                'id_cliente' => $idCliente,
                'fac_descripcion' => $descripcion,
                'fac_fecha_hora' => now(),
                'fac_subtotal' => 0,
                'fac_iva' => 0,
                'estado_fac' => 'ABI',
                'canal_venta' => 'IS', // Sitio
            ]);


            DB::commit();
            return $factura;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



    /**
     * F5.1 – Crear factura
     */
    public static function crearFactura(
        string $idCliente,
        ?string $descripcion
    ) {
        DB::beginTransaction();

        try {

            $idFactura = self::generarSiguienteId();

            $factura = self::create([
                'id_factura' => $idFactura,
                'id_cliente' => $idCliente,
                'fac_descripcion' => $descripcion,
                'fac_fecha_hora' => now(),
                'fac_subtotal' => 0,
                'fac_iva' => 0,
                'estado_fac' => 'ABI',
                'canal_venta' => 'IS'
            ]);

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
            // Llamar al procedimiento almacenado de aprobación de factura
            // NOTA: El SP definido en base de datos es 'aprobar_factura'
            // Casting a ::char para coincidir con la definición estricta 'character'
            DB::select('CALL aprobar_factura(:idFactura::char)', ['idFactura' => $idFactura]);

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
    /**
     * F5.2 – Actualizar cabecera (descripción) y totales
     */
    public static function actualizarCabecera(string $idFactura, ?string $descripcion)
    {
        DB::beginTransaction();

        try {
            $factura = self::with('detalles')
                ->lockForUpdate()
                ->findOrFail($idFactura);

            if ($factura->estado_fac !== 'ABI') {
                throw new \Exception(config('mensajes.M63'));
            }

            // Recalcular subtotal desde los detalles existentes
            $subtotalFactura = DB::table('proxfac')
                ->where('id_factura', $idFactura)
                ->where('estado_pxf', 'ACT')
                ->sum('pxf_subtotal');

            if ($subtotalFactura <= 0) {
                // Opcional: Permitir guardar descripción aunque no tenga productos,
                // o mantener validación si es requisito estricto.
                // throw new \Exception(config('mensajes.M45'));
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

            // Llamar al procedimiento almacenado de anulación
            DB::select('CALL anular_factura(:idFactura::char)', ['idFactura' => $idFactura]);

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
            throw new \Exception(config('mensajes.M57'));
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
            throw new \Exception(config('mensajes.M59'));
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
        return match ($this->estado_fac) {
            'ABI' => '<span class="badge bg-warning text-dark">Abierta</span>',
            'APR' => '<span class="badge bg-success">Aprobada</span>',
            'ANU' => '<span class="badge bg-danger">Anulada</span>',
            default => '<span class="badge bg-secondary">' . $this->estado_fac . '</span>'
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
